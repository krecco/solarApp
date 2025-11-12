package org.bgr.model.api;

import java.time.LocalDateTime;
import java.util.UUID;

public class RepaymentTransactionModel {
    public UUID repaymentId;
    public String repaidTxId;
    public LocalDateTime repaidDate;

    //do value check later
    public Double amout;
}