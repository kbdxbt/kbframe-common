<?php

namespace Modules\Common\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Modules\Common\Rules\Concerns\DataAware;

final class IpRangeRule extends Rule implements DataAwareRule, UncompromisedVerifier
{
    use DataAware;

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool|void
     */
    public function passes($attribute, $value)
    {
        return $this->verify($this->data);
    }

    public function message()
    {
        return '当前来源ip请求过于频繁，暂时被封禁！';
    }

    /**
     * Verify that the given data has not been compromised in data leaks.
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data)
    {
        if (empty($data['ip']) || empty($data['ip_arr'])) {
            return false;
        }

        $ip = $data['ip'];
        $idArr = $data['ip_arr'];

        if (in_array($ip, $idArr)) {
            return true;
        } else {
            $flag = false;
            $ip_pos = explode('.', $ip);
            foreach ($idArr as $val) {
                if (strpos($val, '-') !== false) {//范围
                    $ip_range = explode('-', $val);
                    if (ip2long($ip_range[0]) * -1 >= ip2long($ip) * -1 && ip2long($ip_range[1]) * -1 <= ip2long($ip) * -1) {
                        $flag = true;

                        break;
                    }
                } else {//单个
                    $arr = explode('.', $val);
                    $flag = true; //用于记录循环检测中是否有匹配成功的
                    for ($i = 0; $i < 4; ++$i) {
                        if ($arr[$i] != '*') {//不等于* 就要进来检测，如果为*符号替代符就不检查
                            if (strpos($arr[$i], '/') !== false) {//范围
                                $arr_range = explode('/', $arr[$i]);
                                if ($arr_range[0] <= $ip_pos[$i] && $arr_range[1] >= $ip_pos[$i]) {
                                    break;
                                }
                            }
                            if ($arr[$i] != $ip_pos[$i]) {
                                $flag = false;

                                break; //终止检查本个ip 继续检查下一个ip
                            }
                        }
                    }
                    if ($flag) {//如果是true则终止匹配
                        break;
                    }
                }
            }

            return $flag;
        }
    }
}
