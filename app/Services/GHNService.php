<?php
namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    private const PRODUCT_WEIGHT = 100;
    private const PACKAGE_LENGTH = 10;
    private const PACKAGE_WIDTH = 10;
    private const PACKAGE_HEIGHT = 2;

    protected string $baseUrl;
    protected string $token;
    protected int $shopId;

    public function __construct()
    {
        $this->baseUrl = config('services.ghn.base_url');
        $this->token = config('services.ghn.token') ?? '';
        $this->shopId = (int) config('services.ghn.shop_id', 0);
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withOptions([
                'verify' => filter_var(config('services.ghn.verify_ssl', true), FILTER_VALIDATE_BOOLEAN),
            ])
            ->acceptJson()
            ->timeout(15)
            ->withHeaders([
                'Token'        => $this->token,
                'ShopId'       => $this->shopId,
                'Content-Type' => 'application/json',
            ]);
    }

    // Lấy Tỉnh/Thành
    public function getProvinces(): array
    {
        return $this->get('/master-data/province');
    }

    // Lấy Quận/Huyện
    public function getDistricts(int $provinceId): array
    {
        return $this->get('/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    // Lấy Phường/Xã
    public function getWards(int $districtId): array
    {
        return $this->get('/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    // Tính phí giao hàng
    public function calculateFee(array $params): array
    {
        return $this->post('/v2/shipping-order/fee', array_merge([
            'shop_id' => $this->shopId,
        ], $params));
    }

    // Tạo đơn giao hàng
    public function createOrder(array $orderData): array
    {
        return $this->post('/v2/shipping-order/create', array_merge([
            'shop_id' => $this->shopId,
        ], $orderData));
    }

    public function productWeight(): int
    {
        return self::PRODUCT_WEIGHT;
    }

    public function packageParameters(int $weight = self::PRODUCT_WEIGHT): array
    {
        return [
            'service_type_id' => 2,
            'weight' => $weight > 0 ? $weight : self::PRODUCT_WEIGHT,
            'length' => self::PACKAGE_LENGTH,
            'width' => self::PACKAGE_WIDTH,
            'height' => self::PACKAGE_HEIGHT,
        ];
    }

    // Hủy đơn hàng
    public function cancelOrder(array $orderCodes): array
    {
        return $this->post('/v2/switch-status/cancel', [
            'order_codes' => $orderCodes,
            'shop_id'     => $this->shopId,
        ]);
    }

    protected function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client()->get($uri, $query);

            if (!$response->successful()) {
                Log::warning('GHN GET request failed', [
                    'uri' => $uri,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return [
                    'code' => $response->json('code', $response->status()),
                    'message' => $response->json('message', 'GHN API request failed.'),
                    'data' => $response->json('data'),
                ];
            }

            return $response->json() ?? ['code' => -1, 'message' => 'GHN returned an empty response.'];
        } catch (ConnectionException $exception) {
            Log::error('Unable to connect to GHN', ['uri' => $uri, 'error' => $exception->getMessage()]);

            return ['code' => -1, 'message' => 'Unable to connect to GHN.'];
        }
    }

    protected function post(string $uri, array $payload): array
    {
        try {
            $response = $this->client()->post($uri, $payload);

            if (!$response->successful()) {
                Log::warning('GHN POST request failed', [
                    'uri' => $uri,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return [
                    'code' => $response->json('code', $response->status()),
                    'message' => $response->json('message', 'GHN API request failed.'),
                    'data' => $response->json('data'),
                ];
            }

            return $response->json() ?? ['code' => -1, 'message' => 'GHN returned an empty response.'];
        } catch (ConnectionException $exception) {
            Log::error('Unable to connect to GHN', ['uri' => $uri, 'error' => $exception->getMessage()]);

            return ['code' => -1, 'message' => 'Unable to connect to GHN.'];
        }
    }
}
