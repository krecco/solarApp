package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_repayment_log")

public class SolarPlantRepaymentLog extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID repaymentDataId;
    public UUID plantId;
    public String repaymentPeriod;
    public String documentName;
    public Boolean customerMailSent;
    //I believe that we will have to upgrade this to 2 variables amountProduces / amountExpectedPayment
    public Double amount;
    public Double amountToPay;
    public Double amountProduction;
    public LocalDateTime datumGenerated;
    public LocalDateTime datumCustomerMailSent;
    public LocalDateTime datumPaid;
    public Integer rs;

    //two optional fields
    public String txId;
    public Boolean documentVerified;
    public Boolean paymentVerified;

    public Boolean hasReminders;

    @CreationTimestamp
    public LocalDateTime t0;
}
