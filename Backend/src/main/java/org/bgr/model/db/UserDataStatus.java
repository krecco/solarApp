package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Sort;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;
import org.hibernate.annotations.Type;

import javax.persistence.*;
import javax.validation.constraints.Email;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

@Entity
@Table(name="user_data_status", indexes = {
        @Index(columnList = "id", name = "ndx_user_user_data_status_id"),
        @Index(columnList = "userId", name = "ndx_user_user_data_status_userId"),
    }
)
public class UserDataStatus extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    @Column(unique = true)
    public UUID userId;

    @ColumnDefault("false")
    public boolean basicInfo;

    @ColumnDefault("false")
    public boolean addressInfo;

    @ColumnDefault("false")
    public boolean sepaInfo;
}
