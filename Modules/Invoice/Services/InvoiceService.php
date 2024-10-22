<?php

namespace Modules\Invoice\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Modules\Invoice\Classes\Payable;
use Modules\Invoice\Classes\PayDriver;
use Modules\Invoice\Entities\Invoice;
use Modules\Invoice\Entities\Payment;
//use Shetabit\Shopit\Modules\Invoice\Services\InvoiceService as BaseInvoiceService;
use LogicException;

class InvoiceService /*extends BaseInvoiceService*/
{
    public function payWithWallet(Wallet $user, $payDriver = null)
    {
        $invoice = Invoice::storeByWallet($this->payable, null , Invoice::STATUS_PENDING);

        if ($invoice->getPayAmount() == 0) {
            if ($invoice->gift_wallet_amount != 0){
                Payment::withDrawFromWalletGiftBalance($user, $invoice);
            }
            $transfer = $user->pay($invoice); /*reduce from wallet*/
            $invoice->status = Invoice::STATUS_SUCCESS;
            $invoice->transaction_id = $transfer->id;
            $invoice->save();

            return $this->payable->onSuccessPayment($invoice);
        } else {
            return $this->payable->pay($payDriver);
        }


    }







    // came from vendor ================================================================================================
    protected Payable $payable;
    protected Invoice $invoice;

    public function __construct(Payable $payable)
    {
        $this->payable = $payable;
    }

    /**
     * @return JsonResponse
     * @throws LogicException|BindingResolutionException
     */
    public function pay(PayDriver $payDriver = null)
    {
        $invoice = $this->getInvoice();
        $payDriver = $payDriver ?? app(PayDriver::class);
        $payDriver->setOptions([
            'uuid' => $invoice->payable->id
        ]);
        // Check if the model is payable at the moment and give error with the reason if not
        if (!$this->payable->isPayable()) {
            throw new LogicException($this->payable->isPayableReason());
        }

        $payableAmount = $invoice->getPayAmount();
        $gatewayMakeResponse = $payDriver
            ->make($payableAmount, route('payment.verify', $payDriver->getName()));



        Payment::make($invoice, $gatewayMakeResponse->transactionId, $payDriver->getName());
        $additionalData = $this->payable->additionalDataOnPay();
        if ((float)$invoice->amount === 0.0) {
            return $this->payable->onSuccessPayment($invoice);
//            throw new LogicException('Invoice amount can\' be zero');
        }


        return response()->success(
            __('Connecting to gateway'),
            array_merge([
                'make_response' => $gatewayMakeResponse->getResult(),
                'need_pay' => true
            ], $additionalData)
        );
    }

    public function getInvoice()
    {
        return $this->invoice ?? $this->invoice = ($this->payable->invoices()->pendingPaid()->latest()->first() ?? $this->makeInvoice());
    }

    private function makeInvoice()
    {
        $invoice = new Invoice();
        $invoice->payable()->associate($this->payable);
        $invoice->status = Invoice::STATUS_PENDING;
        $invoice->type = Invoice::getType($this->payable->getPayableAmount(),Invoice::getWalletPayableAmount($this->payable));
        $invoice->amount = config('invoice.float') ? round((float)$this->payable->getPayableAmount(), 2)
            : $this->payable->getPayableAmount();
        $invoice->save();

        return $invoice;
    }

    private function getPayableAmount()
    {
        return  config('invoice.float') ? round((float)$this->payable->getPayableAmount(), 2)
            : $this->payable->getPayableAmount();
    }

}
