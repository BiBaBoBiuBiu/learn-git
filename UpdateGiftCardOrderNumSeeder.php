<?php

use Illuminate\Database\Seeder;

class UpdateGiftCardOrderNumSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run () {
        // 新订单更新订单号
        \App\GiftCardOrderItem::where('card_id', '!=', 0)
            ->get(['order_num', 'card_id'])
            ->keyBy('card_id')
            ->each(function ($item) {
                $card = \App\UserGiftCard::find($item->card_id);
                if (!$card) {
                    file_put_contents(__DIR__.'/card_not_found.log', "gift_card_items card_id \".$item->card_id".PHP_EOL, FILE_APPEND);
                    return;
                }

                DB::enableQueryLog();
                $card->order_num = $item->order_num;
                $card->save();
            });

        self::getRealQuery(DB::getQueryLog());
    }

    /**
     * @param array $queries
     * @return array
     */
    private static function getRealQuery (array $queries) {
        if (!empty($queries)) {
            foreach ($queries as &$query) {
                $query['full_query'] = $fullQuery = vsprintf(str_replace('?', '%s', $query['query']), $query['bindings']);
                if (strstr($fullQuery, 'select')) {
                    continue;
                }

                file_put_contents(__DIR__.'/sql_new', $fullQuery.';'."\n", FILE_APPEND);
            }
        }

        return $queries;
    }
}
