<?php

namespace Pterodactyl\Traits\Services;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Factory;
use Pterodactyl\Exceptions\Service\Egg\Variable\BadValidationRuleException;

trait ValidatesValidationRules
{
    /**
     * @return \Illuminate\Contracts\Validation\Factory
     */
    abstract protected function getValidator(): Factory;

    /**
     * Validate that the rules being provided are valid for Laravel and can
     * be resolved.
     *
     * @param array|string $rules
     *
     * @throws \Pterodactyl\Exceptions\Service\Egg\Variable\BadValidationRuleException
     */
    public function validateRules($rules)
    {
        try {
            $this->getValidator()->make(['__TEST' => 'test'], ['__TEST' => $rules])->fails();
        } catch (BadMethodCallException $exception) {
            $matches = [];
            if (preg_match('/Method \[(.+)\] does not exist\./', $exception->getMessage(), $matches)) {
                throw new BadValidationRuleException(trans('exceptions.nest.variables.bad_validation_rule', [
                    'rule' => Str::snake(str_replace('validate', '', Arr::get($matches, 1, 'unknownRule'))),
                ]), $exception);
            }

            throw $exception;
        }
    }
}
