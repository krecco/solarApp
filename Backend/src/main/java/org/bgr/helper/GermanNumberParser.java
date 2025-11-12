package org.bgr.helper;

import java.text.NumberFormat;
import java.util.Locale;

public class GermanNumberParser {
    public static String getGermanCurrencyFormat(double value, Integer decimals) {
        NumberFormat nf = NumberFormat.getNumberInstance(Locale.GERMAN);
        nf.setMaximumFractionDigits(decimals);
        nf.setMinimumFractionDigits(decimals);
        nf.setGroupingUsed(true);

        return nf.format(value) + " EUR";
        //return "€ " + nf.format(value);
    }

    public static String getGermanNumberFormat(double value,  Integer decimals) {
        NumberFormat nf = NumberFormat.getNumberInstance(Locale.GERMAN);
        nf.setMaximumFractionDigits(decimals);
        nf.setMinimumFractionDigits(decimals);
        nf.setGroupingUsed(true);

        return nf.format(value);
    }
}