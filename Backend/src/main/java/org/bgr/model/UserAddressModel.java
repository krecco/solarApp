package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="user_address", indexes = {
        @Index(columnList = "id", name = "ndx_user_address_id"),
        @Index(columnList = "userId", name = "ndx_user_address_userId"),
        @Index(columnList = "t0", name = "ndx_user_address_t0"),
        @Index(columnList = "rs", name = "ndx_user_address_rs")
    }
)

public class UserAddressModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID userId;

    @NotBlank(message = "street|not_blank")
    public String street;

    @NotBlank(message = "city|not_blank")
    public String city;

    @NotBlank(message = "postNr|not_blank")
    public String postNr;

    public String addressExtra;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("0")
    public Integer rs;
}
