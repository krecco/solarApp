package org.bgr.service;

import org.bgr.model.db.InvestmentModel;
import org.bgr.model.db.InvestmentRepaymentModel;

import javax.enterprise.context.ApplicationScoped;
import javax.transaction.Transactional;
import javax.validation.Valid;
import java.util.UUID;

@ApplicationScoped
public class InvestmentService {
    public void validateInvestment(@Valid InvestmentModel investment) {}

    @Transactional
    public void makeRepaymentsForInvestmentInactive(UUID investmentId) {
        InvestmentRepaymentModel.update("rs = 0 where investmentId = ?1", investmentId);
    }

    @Transactional
    public void insertInvestmentRepayment(InvestmentModel investment) {
        makeRepaymentsForInvestmentInactive(investment.id);

        //insert values
        Double sumRepayment = 0.0;
        Double sumInterests = 0.0;

        Double repaymentPerYear = investment.amount / investment.duration;
        Double remaining = investment.amount;
        Double openAfterYear = investment.amount;

        for (int year = 1; year < investment.duration+1; year++) {
            Double interest = (remaining * investment.interestRate) / 100;
            Double yearlyRePayment = repaymentPerYear + interest;

            InvestmentRepaymentModel repayment = new InvestmentRepaymentModel();

            repayment.investmentId = investment.id;
            repayment.year = year;
            repayment.remainingPayment = remaining;
            repayment.repaymentPerYear = repaymentPerYear;
            repayment.interest = interest;
            repayment.yearlyRePayment = yearlyRePayment;
            repayment.rs = 1;
            repayment.repaid = false;
            repayment.persist();

            sumRepayment += yearlyRePayment;
            sumInterests += interest;
            remaining -= repaymentPerYear;
        }
    }
}
