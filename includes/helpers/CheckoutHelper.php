<?php

class CheckoutHelper {

    /**
     * Calculates the complete price breakdown for the checkout page.
     *
     * @param float $subtotal The subtotal of all items in the cart.
     * @param string|null $country The user's country for tax calculation.
     * @param array $countryTaxRates The list of available tax rates.
     * @return array An associative array with the full price breakdown.
     */
    public static function calculateTotals($subtotal, $country, $countryTaxRates) {
        // Determine the tax rate for the user's country
        $taxPercent = 0;
        if ($country && isset($countryTaxRates[$country])) {
            $taxPercent = $countryTaxRates[$country];
        }

        // Get other fees from the main config file
        $transactionFeePercent = defined('TRANSACTION_FEE_PERCENT') ? TRANSACTION_FEE_PERCENT : 0;
        $maintenanceFeePercent = defined('SERVICE_MAINTENANCE_PERCENT') ? SERVICE_MAINTENANCE_PERCENT : 0;

        // Calculate the fee amounts
        $taxAmount = ($subtotal * $taxPercent) / 100;
        $transactionFee = ($subtotal * $transactionFeePercent) / 100;
        $maintenanceFee = ($subtotal * $maintenanceFeePercent) / 100;

        // Calculate the final total
        $totalAmount = $subtotal + $taxAmount + $transactionFee + $maintenanceFee;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_percent' => $taxPercent,
            'transaction_fee' => $transactionFee,
            'transaction_fee_percent' => $transactionFeePercent,
            'maintenance_fee' => $maintenanceFee,
            'maintenance_fee_percent' => $maintenanceFeePercent,
            'total_amount' => $totalAmount,
        ];
    }
}
