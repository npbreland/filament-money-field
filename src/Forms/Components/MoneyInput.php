<?php

namespace Pelmered\FilamentMoneyField\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Pelmered\FilamentMoneyField\hasMoneyAttributes;
use Pelmered\FilamentMoneyField\MoneyFormatter;

class MoneyInput extends TextInput
{
    use hasMoneyAttributes;

    protected function setUp(): void
    {
        parent::setUp();

        /* $this->rules([ */
        /*     function () { */
        /*         return function (string $attribute, $value, \Closure $fail) { */
        /*             $value = MoneyFormatter::parseDecimal( */
        /*                 $value, */ 
        /*                 'USD', */
        /*                 'en_US' */
        /*             ); */

                    /* if ($value < 0) { */
                    /*     $fail('The :attribute must be greater than 0.'); */
                    /* } */ 
                    /* if ($value > 4_294_967_295) { */
                    /*     $fail('The :attribute must be less than ' . 4_294_967_295 . '.'); */
                    /* } */
                /* }; */
            /* } */
        /* ]); */



        $this->formatStateUsing(function (MoneyInput $component, $state): ?string {
            
            $this->prepare($component);

            $currency = $component->getCurrency();
            $locale = $component->getLocale();

            if (is_null($state)) {
                return '';
            }
            if(!is_numeric($state)) {
                return $state;
            }

            return MoneyFormatter::formatAsDecimal($state, $currency, $locale);
        });

        $this->dehydrateStateUsing(function (MoneyInput $component, $state): string {

            $currency = $component->getCurrency()->getCode();
            $state = MoneyFormatter::parseDecimal($state, $currency, $component->getLocale());

            $this->prepare($component);

            return $state;
        });
    }

    protected function prepare(MoneyInput $component): void
    {
        $formattingRules = MoneyFormatter::getFormattingRules($component->getLocale());
        $this->prefix($formattingRules->currencySymbol);

        if (config('filament-money-field.use_input_mask')) {
            $this->mask(RawJs::make('$money($input, \'' . $formattingRules->decimalSeparator . '\', \'' . $formattingRules->groupingSeparator . '\', ' . $formattingRules->fractionDigits . ')'));
        }
    }
}
