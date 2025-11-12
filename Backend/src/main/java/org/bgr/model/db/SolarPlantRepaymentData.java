package org.bgr.model.db;


import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_repayment_data")

public class SolarPlantRepaymentData extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID plantId;
    public String repaymentPeriod;
    public LocalDate repaymentFromDate;
    public LocalDate repaymentToDate;
    public Double powerProduction;
    public Double powerConsumption;
    public Double consumptionTariff;
    public Double productionTariff;
    public Double productionExtraTariff;

    public Boolean processed;
    public Integer rs;

    @CreationTimestamp
    public LocalDateTime t0;
}
