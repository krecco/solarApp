package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="web_info", indexes = {
        @Index(columnList = "id", name = "ndx_web_info_id"),
        @Index(columnList = "t0", name = "ndx_web_info_t0"),
        @Index(columnList = "t0", name = "ndx_web_info_rs"),
    }
)
public class WebInfoModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public Integer title;
    public String titlePrefix;
    public String titleSuffix;
    public String firstName;
    public String lastName;
    public String email;
    public String phone;
    public LocalDateTime desiredDateTime;
    public Boolean isInvestor;

    public String addressStreet;
    public String addressCity;
    public String addressPostNr;
    public String addressExtra;

    @Column(name="webCalculation", columnDefinition="TEXT")
    public String webCalculation;

    @CreationTimestamp
    public LocalDateTime t0;
    public Integer rs;
}
