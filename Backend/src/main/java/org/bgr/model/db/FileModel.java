package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name="file", indexes = {
        @Index(columnList = "id", name = "ndx_file_id"),
        @Index(columnList = "t0", name = "ndx_file_t0"),
        @Index(columnList = "rs", name = "ndx_file_rs")
    }
)
public class FileModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID idFileContainer;

    @CreationTimestamp
    public LocalDateTime t0;

    public String fileName;
    public String filePath;
    public String fileContentType;
    public Boolean generated;
    public Boolean backendUserUpload;


    public Boolean downloadedByUser;
    public LocalDateTime downloadedByUserDate;
    public UUID downloadedByUserId;

    public Integer rs;
}
