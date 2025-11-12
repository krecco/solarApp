package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_power_bill", indexes = {
        @Index(columnList = "id", name = "ndx_solar_plant_power_bill_id"),
        @Index(columnList = "plantId", name = "ndx_solar_plant_power_bill_plantId"),
        @Index(columnList = "t0", name = "ndx_solar_plant_power_bill_t0"),
        @Index(columnList = "rs", name = "ndx_solar_plant_power_bill_rs")
    }
)
public class SolarPlantPowerBill extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID plantId;
    @NotNull(message = "provider|not_blank")
    public String provider;
    @NotNull(message = "netProvider|not_blank")
    public String netProvider;
    @NotNull(message = "contract|not_blank")
    public String contract;
    public String contract2;

    @NotNull(message = "billNo|not_blank")
    public String billNo;
    @NotNull(message = "billPeriod|not_blank")
    public String billPeriod;

    @NotNull(message = "consumption|not_blank")
    public Double consumption;
    public Double consumption2;

    @NotNull(message = "consumptionValue|not_blank")
    public Double consumptionValue;
    public Double consumptionValue2;


    /* deprecated
    @NotNull(message = "contractKwPrice|not_blank")
    public Double contractKwPrice;
    */

    @CreationTimestamp
    public LocalDateTime t0;

    public Integer rs;
}
