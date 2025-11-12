package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="auth_user_verify", indexes = {
        @Index(columnList = "id", name = "ndx_auth_user_verify_id"),
        @Index(columnList = "userId", name = "ndx_auth_user_verify_userId"),
        @Index(columnList = "t0", name = "ndx_user_auth_user_verify_t0"),
        @Index(columnList = "rs", name = "ndx_user_auth_user_verify_rs")
    }
)
public class AuthUserVerify extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID userId;
    public String email;
    public String type;
    public String token;

    public Integer rs;

    @CreationTimestamp
    public LocalDateTime t0;
}
