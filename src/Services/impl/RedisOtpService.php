<?php


namespace App\Services\impl;


use App\Helpers\RabbitMQHelper;
use App\Services\OtpService;
use App\Services\TemplateService;
use Exception;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Log;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use YaangVu\Constant\RedisConstant;
use YaangVu\Exceptions\BaseException;

class RedisOtpService implements OtpService
{
    use RabbitMQHelper;

    private int $otp = 666888;

    private Connection $redisConnection;

    private TemplateService $templateService;

    private bool $emailOtpAble = true;

    private bool $smsOtpAble = true;

    public function __construct()
    {
        $this->redisConnection = Redis::connection();
        $this->templateService = new EmailTemplateService();
    }

    public function create(string|int $key): bool
    {
        $otp = rand(100000, 999999);
        $ttl = RedisConstant::TTL;

        try {
            $this->redisConnection->set($key, $otp, 'ex', $ttl);
            $this->sendOtp($otp);

            return true;
        } catch (Exception $exception) {
            throw new BaseException($exception->getMessage(), $exception);
        }
    }

    /**
     * @param string|int $key
     * @param int|string $otp
     *
     * @return bool
     */
    public function verify(string|int $key, int|string $otp): bool
    {
        if ($otp == $this->otp) // Hard code OTP for testing
            return true;

        if ($otp === $this->get($key)) {
            $this->redisConnection->command('DEL', [$key]);

            return true;
        }

        return false;
    }

    /**
     * @param int|string $key
     *
     * @return int|string|null
     */
    public function get(int|string $key): int|string|null
    {
        return $this->redisConnection->get($key);
    }

    /**
     * @param int|string $otp
     *
     * @throws Exception
     */
    public function sendOtp(int|string $otp): void
    {
        $username     = request()->header('X-username');
        $replacements = [
            '{{otp}}'      => $otp,
            '{{username}}' => $username
        ];

        // Send OTP via email
        $email = request()->header('X-email');

        if ($email && $this->emailOtpAble) {
            // Get Email template
            $this->templateService = new EmailTemplateService();
            $template              = $this->templateService->get(1);
            $emailBody             = $this->_updateTemplate($template, $replacements);

            $body = [
                "subject"    => "[SIS-OTP] Security verification code",
                "body"       => $emailBody,
                "recipients" => [
                    $email
                ],
            ];

            $this->pushToExchange($body, 'EMAIL', AMQPExchangeType::DIRECT, 'sendgrid');
        } else {
            Log::info("Can not find email to send OTP or email OTP not active");
        }

        // Send OTP via SMS
        // $this->templateService = new SmsTemplateService();
        // if ($this->smsOtpAble) {
        // Do something
        // } else {
        // Do something
        // }
    }

    /**
     * @param string $template
     * @param array  $replacements
     *
     * @return array|string
     */
    private function _updateTemplate(string $template, array $replacements): array|string
    {
        foreach ($replacements as $search => $replacement)
            $template = str_replace($search, $replacement, $template);

        return $template;
    }
}
