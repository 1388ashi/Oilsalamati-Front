<?php

use Illuminate\Support\Facades\DB;
use Shetabit\Shopit\Database\CreateOrderReportsView as BaseCreateOrderReportsView;

class CreateOrderReportsView extends BaseCreateOrderReportsView {
    /**
     * @return void
     */
    public function up(): void
    {
    DB::statement(
        <<<END
                 CREATE OR REPLACE VIEW order_reports_view AS
        (SELECT
        o.*,
        (
            SUM(oi.amount * COALESCE(oi.quantity,0)) + COALESCE(o12.total, 0) + o.shipping_amount - o.discount_amount
        ) AS total,
        (
            SUM(
                oi.discount_amount * oi.quantity
            ) + COALESCE(
                o12.not_coupon_discount_amount,
                0
            )
        ) AS not_coupon_discount_amount,
        CONCAT(
            GROUP_CONCAT(
                '"',
                JSON_VALUE(oi.extra, '$.color.id'),
                '-',
                oi.quantity,
                '"'
            ),
            ',',
            COALESCE(o12.color_ids, '')
        ) AS color_ids,
        CONCAT(
            GROUP_CONCAT(
                CONCAT(
                    JSON_EXTRACT(oi.extra, '$.attributes[*].name'),
                    '||',
                    JSON_EXTRACT(
                        oi.extra,
                        '$.attributes[*].value'
                    ),
                    '---',
                    oi.quantity
                ) SEPARATOR '!#!'
            ),
            '!#!',
            COALESCE(o12.attribute_ids, '')
        ) AS attribute_ids,
        (
            SUM(COALESCE(oi.quantity,0)) + COALESCE(o12.order_items_count, 0)
        ) AS order_items_count,
        (
            COUNT(oi.id) + COALESCE(
                o12.order_items_unique_count,
                0
            )
        ) AS order_items_unique_count,
        (
            CONCAT(
                GROUP_CONCAT(
                    CONCAT(
                        '"',
                        oi.product_id,
                        '-',
                        oi.quantity,
                        '"'
                    )
                ),
                ',',
                COALESCE(o12.product_ids, '')
            )
        ) AS product_ids
        FROM
        orders o
        LEFT JOIN order_items oi ON
        o.id = oi.order_id AND oi.status = 1 and oi.canceled = 0
        LEFT JOIN(
        SELECT
            o2.parent_id AS parent_id,
            (
                SUM(oi2.amount * oi2.quantity) + o2.shipping_amount - o2.discount_amount
            ) AS total,
            (
                SUM(
                    oi2.discount_amount * oi2.quantity
                )
            ) AS not_coupon_discount_amount,
            SUM(oi2.quantity) AS order_items_count,
            COUNT(oi2.id) AS order_items_unique_count,
            GROUP_CONCAT(
                '"',
                JSON_VALUE(oi2.extra, '$.color.id'),
                '-',
                oi2.quantity,
                '"'
            ) AS color_ids,
            GROUP_CONCAT(
                CONCAT(
                    JSON_EXTRACT(
                        oi2.extra,
                        '$.attributes[*].name'
                    ),
                    '||',
                    JSON_EXTRACT(
                        oi2.extra,
                        '$.attributes[*].value'
                    ),
                    '---',
                    oi2.quantity
                ) SEPARATOR '!#!'
            ) AS attribute_ids,
            GROUP_CONCAT(
                CONCAT(
                    '"',
                    oi2.product_id,
                    '-',
                    oi2.quantity,
                    '"'
                ) SEPARATOR ','
            ) AS product_ids
        FROM
            orders o2
        INNER JOIN order_items oi2 ON
            o2.id = oi2.order_id AND oi2.status = 1 and oi2.canceled = 0
        WHERE
            o2.parent_id is NOT null and o2.status in ('new', 'delivered', 'in_progress', 'reserved')
        GROUP BY
            o2.parent_id
        ) o12
        ON
            o12.parent_id = o.id
        WHERE
            o.parent_id IS NULL
        GROUP BY
            o.id  
        ORDER BY `o`.`id`  DESC
        )
        END
                );
}
}
