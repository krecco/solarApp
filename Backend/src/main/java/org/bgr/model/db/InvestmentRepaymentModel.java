package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="investment_repayment", indexes = {
        @Index(columnList = "investmentId", name = "ndx_investment_repayment_id")
    }
)
public class InvestmentRepaymentModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID investmentId;
    public Integer year;
    public Double remainingPayment;
    public Double repaymentPerYear;
    public Double interest;
    public Double yearlyRePayment;
    public Boolean repaid;
    public String repaidTxId;
    public LocalDateTime repaidDate;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("0")
    public Integer rs;
}