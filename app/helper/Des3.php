<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/10/26
 * Time: 13:47
 */

namespace app\helper;


class Des3
{
    // base64编码后的3des密钥
    private $key = 'ZcVuACADiPsVDDp9QsqnjRIIYFewM6rT';

    // 混淆向量
    // private $iv = str_repeat(chr(0), 8); // 声明属性时不能调用函数
    private $iv;

    // 数据库配置
    protected $database;

    // 本地加密密钥
    protected $local_key = 'ZcVuACADiPsVDDp9QsqnjRIIYFewM6rT';

    public function __construct()
    {
        $this->iv = str_repeat(chr(0), 8);
        $this->key = base64_decode($this->key);
    }

    public function decrypt($encrypted, $local = false)
    {
        // 对使用base64编码的数据进行解码
        $encrypted = base64_decode($encrypted); //base64_decode/bin2hex

        // 获取key，若key不足24位用0补位
        if ($local) {
            $key = str_pad($this->local_key, 24, 0); //3DES加密将8改为24
        } else {
            $key = str_pad($this->key, 24, 0); //3DES加密将8改为24
        }

        // 打开算法和模式对应的模块
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');//MCRYPT_DES/MCRYPT_3DES

        // 获取初始向量
        if ($this->iv == '') {
            $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        } else {
            $iv = $this->iv;
        }

        // 返回打开的模式所能支持的最长密钥
        $ks = mcrypt_enc_get_key_size($td);

        // 初始化加密所需的缓冲区
        @mcrypt_generic_init($td, $key, $iv);

        // 解密数据
        $decrypted = mdecrypt_generic($td, $encrypted);

        // 对加密模块进行清理工作
        mcrypt_generic_deinit($td);

        // 关闭加密模块
        mcrypt_module_close($td);

        // 对需要解密后的数据进行还原
        $y = $this->pkcs5_unpad($decrypted);

        return $y;
    }

    // 将加密文本补全至加密分组大小的整数倍
    protected function pkcs5_pad($text, $blocksize)
    {
        // 计算 文本补全到$blocksize的整数倍 所需要的位数
        $pad = $blocksize - (strlen($text) % $blocksize);

        // 给需要加密的文本后面添加$pad位 字符chr($pad)
        return $text . str_repeat(chr($pad), $pad);
    }


    protected function pkcs5_unpad($text)
    {
        // 获取最后一个字符的ASCII值，也就是原文本补全位的数量
        $pad = ord($text{strlen($text) - 1});

        // 补全的位数比加密后的文本还大，就返回false
        if ($pad > strlen($text)) {
            return false;
        }

        //
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }

    protected function PaddingPKCS7($data)
    {
        $block_size = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);//MCRYPT_DES/MCRYPT_3DES
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }
}