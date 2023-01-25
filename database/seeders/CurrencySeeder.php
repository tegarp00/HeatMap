<?php

namespace Database\Seeders;

use App\Helpers\HttpClient;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $payload = $this->filterCurrencies();

        foreach($payload as $data) {
          Currency::query()->create($data);
        }

    }

  public function filterCurrencies()
  {
    $query = [
            'base' => "idr",
        ];

        
        $urlSymbols = url('https://api.exchangerate.host/symbols');
        $responseSymbol = HttpClient::fetch(
            "GET",
            $urlSymbols,
        );

        $urlCurrency = url('https://api.exchangerate.host/latest') . '?' . http_build_query($query, ',&');

        $response = HttpClient::fetch(
            "GET",
            $urlCurrency,
        );

    $restName = [];

    $name = $response['rates'];
    foreach($name as $k => $v) {

      $r = [];
      $r['name'] = $k;
      $r['fullname'] = $responseSymbol['symbols'][$k]['description'];
      $r['rate'] = $v;

      array_push($restName, $r);
    }

    return $restName;

  }

}
