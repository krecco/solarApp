package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="admin_settings", indexes = {
        @Index(columnList = "id", name = "ndx_admin_settings_id"),
        @Index(columnList = "t0", name = "ndx_admin_settings_t0"),
        @Index(columnList = "rs", name = "ndx_admin_settings_rs"),
        @Index(columnList = "active", name = "ndx_admin_settings_active")
    }
)
public class SettingsModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String title;
    public Boolean active;

    public Boolean directBuy;

    public Double rateConsumption;
    public Double rateExcessProduction;
    public Double rateExcessProductionExternal;
    public Double kWpPrice;
    public Double kWpPriceSurcharge;
    public Double kWpPriceSurchargeDirectBuy;
    public Integer planningFlatRateMin;
    public Integer planningFlatRate;
    public Integer planningFlatRateMax;
    public Integer serviceFeeMin;
    public Integer serviceFee;
    public Integer serviceFeeMax;
    public Double interestRate;
    public Double spikePrice;
    public Double surgePrice;
    public Double pricePowerLanAdapter;
    public Double kwpSizePewKw;
    public Double moduleSize;
    public Double buildingPermitCosts;

    public Double subventionTo10Kw;
    public Double subventionFrom10Kw;

    public String subventionInstitution;

    @CreationTimestamp
    public LocalDateTime t0;

    public Integer rs;
}