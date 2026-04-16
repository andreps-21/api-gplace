<?php

use Carbon\Carbon;

if (!function_exists('carbon')) {
    /**
     * Retornar instância de data.
     *
     * @param mixed $date
     * @return Carbon\Carbon
     */
    function carbon($date = null)
    {
        if (!empty($date)) {
            if ($date instanceof DateTime) {
                return Carbon::instance($date);
            }

            return Carbon::parse(date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $date))));
        }

        return Carbon::now();
    }
}


if (!function_exists('removeMask')) {
    /**
     * Remover máscara.
     *
     * @param string $str
     * @return string
     */
    function removeMask(string $str)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $str);
    }
}

if (!function_exists('insertMask')) {
    /**
     * Remover máscara.
     *
     * @param string $str
     * @return string
     */
    function insertMask(string $str, string $mask)
    {
        $str = str_replace(" ", "", $str);

        for ($i = 0; $i < strlen($str); $i++) {
            $mask[strpos($mask, "#")] = $str[$i];
        }

        return $mask;
    }
}

if (!function_exists('phoneMask')) {
    /**
     * Remover máscara.
     *
     * @param string $str
     * @return string
     */
    function phoneMask(?string $str)
    {
        if (!$str) {
            return $str;
        }

        $mask = '(##) #####-#####';

        $str = str_replace(" ", "", $str);

        for ($i = 0; $i < strlen($str); $i++) {
            $mask[strpos($mask, "#")] = $str[$i];
        }

        return $mask;
    }
}

if (!function_exists('nifMask')) {
    /**
     * Remover máscara.
     *
     * @param string $str
     * @return string
     */
    function nifMask(?string $str)
    {
        if (!$str) {
            return $str;
        }

        if (strlen($str) == 11) {
            $mask = '###.###.###-##';
        } else {
            $mask = '##.###.###/####-##';
        }

        $str = str_replace(" ", "", $str);

        for ($i = 0; $i < strlen($str); $i++) {
            $mask[strpos($mask, "#")] = $str[$i];
        }

        return $mask;
    }
}

if (!function_exists('floatToMoney')) {
    /**
     * Transforma float do banco em reais.
     *
     * @param string $str
     * @return string
     */
    function floatToMoney($value)
    {
        return number_format($value, 2, ',', '.');
    }
}

if (!function_exists('moneyToFloat')) {
    /**
     * Converte reais para float.
     *
     * @param string $str
     * @return string
     */
    function moneyToFloat($value)
    {
        if (!$value) return 0;

        $source = array('.', ',');
        $replace = array('', '.');
        return str_replace($source, $replace, $value);
    }
}

if (!function_exists('settings')) {

    function settings($key = null, $default = null)
    {
        if ($key === null) {
            return app(App\Models\Settings::class);
        }

        return app(App\Models\Settings::class)->get($key, $default);
    }
}


function moeda($get_valor)
{
    if (!$get_valor) return 0;

    $source = array('.', ',');
    $replace = array('', '.');
    $valor = str_replace($source, $replace, $get_valor);
    return $valor;
}


function clean($string)
{
    return preg_replace('/[^A-Za-z0-9]/', '', $string);
}

function money($get_valor)
{
    $valor = number_format($get_valor, 2, ',', '.');
    return $valor;
}
