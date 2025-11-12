package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="solar_plant_property_owner", indexes = {
        @Index(columnList = "id", name = "ndx_solar_plant_property_owner_id"),
        @Index(columnList = "t0", name = "ndx_solar_plant_property_owner_t0"),
        @Index(columnList = "rs", name = "ndx_solar_plant_property_owner_rs"),
        @Index(columnList = "plantId", name = "ndx_solar_plant_property_owner_plantId")
    }
)
public class SolarPlantPropertyOwnerModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String person;
    public UUID plantId;

    @CreationTimestamp
    public LocalDateTime t0;

    public Integer rs;
}