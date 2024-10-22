<?php

namespace Modules\Core\Services;

//use Shetabit\Shopit\Modules\Core\Services\QueryFiltersService as BaseQueryFiltersService;




use Modules\Core\Helpers\Helpers;

class QueryFiltersService
{
    protected $query;
    protected $exactSearchFields;
    protected $prefix;

    public function __construct($query, $prefix = null, $exactSearchFields = [])
    {
        $this->query = $query;
        $this->prefix = $prefix;
        $this->exactSearchFields = $exactSearchFields;
    }

    public function applyAllFilters()
    {
        $this->searchFilters(8);
        $this->dateFilter();
        $this->sortFilter();
    }

    public function searchFilters($paramsCount = 8)
    {
        $prefix = empty($this->prefix) ? '' : $this->prefix . '.';
        for ($i = 1; $i <= $paramsCount; $i++) {
            $search = \request('search' . $i, false);
            $searchBy = \request('searchBy' . $i, false);
            if (mb_strlen($search) > 0 && strlen($searchBy) > 0) {
                if (str_contains($searchBy, '_id') || in_array($searchBy, $this->exactSearchFields) || $searchBy == 'id') {
                    $this->query->where($prefix . $searchBy, '=', $search);
                } else {
                    $this->query->where($prefix . $searchBy, 'LIKE', '%' . $search . '%');
                }
            }
        }

        return $this->query;
    }

    public function dateFilter()
    {
        $request = request();
        Helpers::toCarbonRequest(['start_date', 'end_date'], $request);
        if ($request->filled('start_date')) {
            $this->query->where('created_at', '>', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $this->query->where('created_at', '<', $request->end_date);
        }

        return $this->query;
    }

    public function sortFilter()
    {
        if (request('sort_by', false)) {
            $order = \request('sort_desc') ? 'desc' : 'asc';
            if (class_basename($this->query) == 'Builder') {
                $this->query->getQuery()->orders = null;
            } else {
                // is relationship
                if (method_exists($this->query, 'getBaseQuery')) {
                    $this->query->getBaseQuery()->orders = null;
                } else {
                    $this->query->getQuery()->orders = null;
                }
            }

            return $this->query->orderBy(request('sort_by'), $order);
        }

        return $this->query;
    }
}
