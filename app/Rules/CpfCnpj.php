<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfCnpj implements Rule
{
    /** @var 'empty'|'length'|'cpf'|'cnpj'|'generic' */
    private string $failure = 'generic';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->failure = 'generic';

        if ($value === null || $value === '') {
            $this->failure = 'empty';

            return false;
        }

        $digits = preg_replace('/\D/', '', (string) $value);
        $len = strlen($digits);

        if ($len === 11) {
            if (! $this->validateCpf($digits)) {
                $this->failure = 'cpf';
            }

            return $this->failure === 'generic';
        }

        if ($len === 14) {
            if (! $this->validateCnpj($digits)) {
                $this->failure = 'cnpj';
            }

            return $this->failure === 'generic';
        }

        $this->failure = 'length';

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return match ($this->failure) {
            'empty' => 'Preencha o CPF ou o CNPJ.',
            'length' => 'Use 11 dígitos para CPF ou 14 dígitos para CNPJ.',
            'cpf' => 'O CPF informado é inválido.',
            'cnpj' => 'O CNPJ informado é inválido.',
            default => 'O CPF/CNPJ informado é inválido.',
        };
    }

    protected function validateCpf($cpf)
    {

        // Verificar se foi informado
        if (empty($cpf))
            return false;

        // Remover caracteres especias
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se o numero de digitos informados
        if (strlen($cpf) != 11)
            return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf))
            return false;

        // Calcula os digitos verificadores para verificar se o
        // CPF é válido
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    protected function validateCnpj($cnpj)
    {

        // Extrai os números
        $cnpj = preg_replace('/[^0-9]/is', '', $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica sequência de digitos repetidos. Ex: 11.111.111/111-11
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Valida dígitos verificadores
        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += $cnpj[$i] * $m;
                $m = ($m == 2 ? 9 : --$m);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$i] != $d) {
                return false;
            }
        }
        return true;
    }
}
