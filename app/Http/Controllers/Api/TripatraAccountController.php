<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TripatraAccountController extends Controller
{
    public $accounts = [];

    public function getAccounts()
    {
        // Cache the accounts data for 1 hour
        $accountsCollection = Cache::remember('accounts', 60 * 60, function () {
            return $this->fetchAccountsFromAPI();
        });

        return $accountsCollection;
    }

    public function searchAccounts($searchTerm, $searchBy = 'displayName')
    {
        $collection = $this->getAccounts();
        return $collection->filter(function ($item) use ($searchTerm, $searchBy) {
            return str_contains(strtolower($item[$searchBy] ?? ''), strtolower($searchTerm));
        });
    }

    private function fetchAccountsFromAPI(): Collection
    {
        $graphApi = new GraphApiController();
        $account = $graphApi->getAccounts();
        $data = $account->original['value'];
        $this->accounts = $this->collectAccounts($data);

        // Handle pagination
        while (Arr::has($account->original, '@odata.nextLink')) {
            $nextUrl = $account->original['@odata.nextLink'];
            $account = $graphApi->getAccounts($nextUrl);
            $data = $account->original['value'];
            $this->accounts = $this->collectAccounts($data);
        }

        return collect($this->accounts);
    }

    private function collectAccounts(array $data): array
    {
        foreach ($data as $item) {
            $this->accounts[] = $item;
        }

        return $this->accounts;
    }
}
