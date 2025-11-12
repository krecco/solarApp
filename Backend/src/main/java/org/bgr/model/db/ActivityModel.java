package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="activity", indexes = {
        @Index(columnList = "id", name = "ndx_activity_id"),
        @Index(columnList = "t0", name = "ndx_activity_t0"),
        @Index(columnList = "userId", name = "ndx_activity_userId"),
        @Index(columnList = "contentType", name = "ndx_activity_contentType"),
        @Index(columnList = "showOnUserDashboard", name = "ndx_activity_showOnUserDashboard"),
    }
)
public class ActivityModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public String title;
    public String content;

    public UUID userId;
    public UUID contentId;
    public UUID parentContentId;
    public String contentType;
    public String notificationType;
    public Boolean showOnUserDashboard;
    public String filename;

    public String createdBy;
    public UUID createdById;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("0")
    public Integer rs;
}
