package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_repayment_log_reminder")

public class SolarPlantRepaymentLogReminder extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID repaymentLogId;
    public String documentName;
    public Boolean customerMailSent;
    public Double amount;
    public LocalDateTime datumGenerated;
    public LocalDateTime datumPaid;

    public Integer reminderNr;

    public Integer rs;

    public String txId;
    public Boolean paymentVerified;

    @CreationTimestamp
    public LocalDateTime t0;
}
