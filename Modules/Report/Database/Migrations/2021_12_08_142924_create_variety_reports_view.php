<?php

use Illuminate\Support\Facades\DB;
use Shetabit\Shopit\Database\CreateVarietyReportsView as BaseCreateVarietyReportsView;

class CreateVarietyReportsView extends BaseCreateVarietyReportsView {
/**
     * @return void
     */
    public function up(): void
    {
        DB::statement(
<<<END
         CREATE OR REPLACE VIEW variety_reports_view AS
(
    SELECT v.id, v.name, v.product_id, v.discount_type, v.discount, v.color_id,
    v.max_number_purchases, v.barcode, v.SKU, v.price,
    v.deleted_at, oi.amount as amount, oi.quantity AS quantity,
        (IF(o.parent_id IS NOT NULL, o2.created_at, o.created_at)) as created_at,
          (IF(o.parent_id IS NOT NULL, o2.id, o.id)) AS order_id,
          (IF(o.parent_id IS NOT NULL, o2.status, o.status)) AS status,
          o.customer_id as customer_id, oi.flash_id as flash_id, oi.extra as extra,
          o.address
   from varieties v left join order_items oi on v.id = oi.variety_id
                    inner join orders o on oi.order_id = o.id left join orders o2 on o.parent_id is not null and o.parent_id = o2.id
   where oi.status = 1 and oi.canceled = 0 AND (o.status IN ('new', 'delivered', 'in_progress', 'reserved'))
)
END
        );
    }

}

/*
SELECT v.id, v.name, v.product_id, v.discount_type, v.discount, v.color_id,
 v.max_number_purchases, v.barcode, v.SKU, v.price,
 v.deleted_at, oi.amount as amount, oi.quantity AS quantity,
       o.created_at             as created_at,
       (IF(o.parent_id IS NOT NULL, o2.id, o.id)) AS order_id,
       (IF(o.parent_id IS NOT NULL, o2.status, o.status)) AS status,
       o.customer_id as customer_id, oi.flash_id as flash_id, oi.extra as extra,
       o.address
from varieties v left join order_items oi on v.id = oi.variety_id
                 inner join orders o on oi.order_id = o.id left join orders o2 on o.parent_id = o2.id
where oi.status = 1 AND (o.status IN ('new', 'delivered', 'in_progress', 'reserved'));
*/
