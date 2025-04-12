<?php
namespace App\Logging;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use App\Models\Log as LogModel;

class MysqlLoggerHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        try{
            if(isset($record['context']['exception']) && is_object($record['context']['exception'])){
                $record['context']['exception'] = (array)$record['context']['exception'];
            }
            $record['request_data'] = request()->all() ??[];
            $log = [
                'title' => $record['message'],
                'level' => $record['level_name'],
                'host' => $record['request_host'] ?? request()->getSchemeAndHttpHost(),
                'uri' => $record['request_uri'] ?? request()->getRequestUri(),
                'method' => $record['request_method'] ?? request()->getMethod(),
                'ip' => $this->getClientRealIp(),
                'data' => json_encode($record['request_data']) ,
                'context' => isset($record['context']) ? json_encode($record['context']) : '',
                'created_at' => strtotime($record['datetime']),
                'updated_at' => strtotime($record['datetime']),
            ];

            LogModel::insert(
                $log
            );
        }catch (\Exception $e){
            Log::channel('daily')->error($e->getMessage().$e->getFile().$e->getTraceAsString());
        }
    }
    protected function getClientRealIp(): string
    {
        // Check for Cloudflare's CF-Connecting-IP header
        if (request()->hasHeader('CF-Connecting-IP')) {
            return request()->header('CF-Connecting-IP');
        }

        // Check for X-Forwarded-For header
        if (request()->hasHeader('X-Forwarded-For')) {
            // X-Forwarded-For may contain multiple IPs, take the first one
            $ipList = explode(',', request()->header('X-Forwarded-For'));
            return trim($ipList[0]);
        }

        // Fallback to REMOTE_ADDR if no proxy headers are found
        return request()->getClientIp();
    }
}
