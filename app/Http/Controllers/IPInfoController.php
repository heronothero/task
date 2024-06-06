<?php

namespace App\Http\Controllers;
use App\Jobs\IPInfoWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IPInfoController extends Controller
{
    public function getInfo(Request $request)
    {
        $ip = $request->ip;
        //проверка на наличия айпи в кэшк
        $cachedInfo = Cache::get($ip);
        if ($cachedInfo) {
            return response()->json($cachedInfo);
        }
        IPInfoWorker::dispatch($ip);
        //если информациив кэше нет, то запрашиваем данные с внешнего сервиса
        $response = Http::get("https://ip-api.com/json/{$ip}");

        //проверка
        if ($response->successful()) {
            $data = $response->json();
            //сохранение в кэше на 24чвса
            Cache::put($ip, $data, 1440);
            return response()->json($data);
        } else {
            //если ошбка
            return response()->json(['error' => 'Failed to fetch IP information'], $response->status());
        }
    }
}
