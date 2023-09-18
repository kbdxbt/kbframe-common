<?php

declare(strict_types=1);

namespace Modules\Common\Console;

use Guanguans\Notify\Clients\DingTalkClient;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Console\Input\InputOption;

class QueueProcessMonitor extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'queue:process:monitor';

    /**
     * The console command description.
     */
    protected $description = 'Queue process monitoring';

    protected $maxLimitLength = 1000; // 队列堆积最大长度

    protected $failIntervalTime = 3600; // 失败队列查询间隔时间, 单位：s

    public function handle(): void
    {
        //        $this->info

        $this->task('Long task.', function () {
            sleep(3);

            return true;
        }, 'sleeping...');

        exit;
        $this->task('Successful task.', function () {
            //            throw new \Exception(1234);
            return true;
        });

        $this->task('Successful task.', function () {
            //            throw new \Exception(1234);
            return true;
        });

        echo 22;

        exit;
        //        $queue = $this->option('queue');
        //        $type = $this->option('type');
        //        $webhookType = $this->option('webhook_type');
        //        $webhookKey = $this->option('webhook_key');
        //
        //        $key = 1;
        //
        //        $queueKeys = Arr::wrap($key);
        //
        //        $sendContent = $this->_handleQueueInfo($queueKeys, $type);
        //
        //        if ($webhookType && $webhookKey) {
        //            $res = (new DingTalkClient())->setSecret($key);
        //            //$res = (new WeWorkClient())->setToken($key);
        //
        //            $jsonMsg = json_decode($res['msg'], true);
        //
        //            if ($jsonMsg['errcode'] != 0) {
        //                $this->info('队列进程监控通知发送成功');
        //            } else {
        //                $this->error('队列进程监控通知发送失败，失败原因：'.$res['msg']);
        //            }
        //        } else {
        //            dump($sendContent);
        //        }
    }

    //    /**
    //     * 处理队列信息
    //     *
    //     * @param  array  $queueKeys
    //     * @return string
    //     */
    //    private function _handleQueueInfo($queueKeys, $type)
    //    {
    //        $limitMessage = $failMessage = $lengthMessage = [];
    //
    //        foreach ($queueKeys as $key) {
    //            $size = Queue::size($key);
    //
    //            if ($size > $this->maxLimitLength) {
    //                $limitMessage[] = sprintf('%s：%s', $key, $size);
    //            }
    //
    //            $failCount = DB::table(config('queue.failed.table'))->where([
    //                ['queue', '=', $key],
    //                ['failed_at', '>=', now()->subSeconds($this->failIntervalTime)],
    //            ])->count();
    //            if ($failCount > 0) {
    //                $failMessage[] = sprintf('%s：%s', $key, $failCount);
    //            }
    //
    //            if ($size > 0) {
    //                $lengthMessage[] = sprintf('%s：%s', $key, $size);
    //            }
    //        }
    //
    //        $queueMessage = '';
    //        if ((! $type || $type == 1) && $limitMessage) {
    //            $queueMessage .= '【队列堆积超过预警】:'."\n\n".implode("\n\n", $limitMessage)."\n\n";
    //        }
    //        if ((! $type || $type == 2) && $failMessage) {
    //            $queueMessage .= '【队列近'.$this->failIntervalTime.'秒内失败次数预警】:'."\n\n".implode("\n\n", $failMessage)."\n\n";
    //        }
    //        if ((! $type || $type == 3) && $lengthMessage) {
    //            $queueMessage .= '【队列长度监控】:'."\n\n".implode("\n\n", $lengthMessage)."\n\n";
    //        }
    //        if (! $queueMessage) {
    //            $queueMessage .= '暂无监控数据';
    //        }
    //
    //        $sendMessage = '队列进程监控'.now()." \n\n".$queueMessage;
    //
    //        return $sendMessage;
    //    }
    //
    //    /**
    //     * Get the console command options.
    //     *
    //     * @return array
    //     */
    //    protected function getOptions()
    //    {
    //        return [
    //            ['key', 'k', InputOption::VALUE_REQUIRED, 'Flag to queue key', null],
    //            ['type', 't', InputOption::VALUE_REQUIRED, 'Flag to monitor type[1:limit;2:fail;3:length]', null],
    //            ['webhook_type', null, InputOption::VALUE_REQUIRED, 'Flag to webhook type[1:weixin;2:dingtalk]', null],
    //            ['webhook_key', null, InputOption::VALUE_REQUIRED, 'Flag to webhook key', null],
    //        ];
    //    }
}
