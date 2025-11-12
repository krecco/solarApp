package org.bgr.model;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnTransformer;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="user_sepa_permission", indexes = {
        @Index(columnList = "id", name = "ndx_user_sepa_permission_id"),
        @Index(columnList = "userId", name = "ndx_user_sepa_permission_userId"),
        @Index(columnList = "t0", name = "ndx_user_sepa_permission_t0"),
        @Index(columnList = "rs", name = "ndx_user_sepa_permission_rs")
    }
)
public class UserSepaPermissionModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    //@Column(unique = true)
    public UUID userId;

    //add extension to db | CREATE EXTENSION pgcrypto;
    @NotBlank(message = "account|not_blank")
    @ColumnTransformer(read = "pgp_sym_decrypt(account, 'mySecretKey')", write = "pgp_sym_encrypt(?, 'mySecretKey')")
    @Column(columnDefinition = "bytea")
    public String account;

    @NotBlank(message = "bic|not_blank")
    public String bic;

    @NotBlank(message = "fullName|not_blank")
    public String fullName;

    /*
    @ColumnTransformer(read = "pgp_sym_decrypt(encryptedField, 'mySecretKey')", write = "pgp_sym_encrypt(?, 'mySecretKey')")
    @Column(columnDefinition = "bytea")
    public String encryptedField;
    */

    @CreationTimestamp
    public LocalDateTime t0;

    public Integer rs;
}