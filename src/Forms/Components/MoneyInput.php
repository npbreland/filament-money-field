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

    public function maxValue($max): static
    {
        $this->rule('max_value', function ($attribute, $value, $fail) use ($max) {

            $value = MoneyFormatter::parseDecimal(
                $value, 
                $this->getCurrency()->getCode(),
                $this->getLocale()
            );

            if ($value > $max) {
                $fail('The :attribute must be less than ' . $max . '.');
            }
        });
        return $this;
    }

    public function minValue($min): static
    {
        $this->rule('min_value', function ($attribute, $value, $fail) use ($min) {

            $value = MoneyFormatter::parseDecimal(
                $value, 
                $this->getCurrency()->getCode(),
                $this->getLocale()
            );

            if ($value < $min) {
                $fail('The :attribute must be greater than ' . $min . '.');
            }
        });
        return $this;
    }
}
