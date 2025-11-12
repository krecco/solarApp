package org.bgr.model.db;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import org.bgr.helper.NativeQueryToJson;
import org.bgr.model.SolarPlantModel;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

@Entity
@Table(name="investment", indexes = {
        @Index(columnList = "id", name = "ndx_investment_id"),
        @Index(columnList = "t0", name = "ndx_investment_t0"),
        @Index(columnList = "userId", name = "ndx_investment_userId"),
        @Index(columnList = "rs", name = "ndx_investment_rs")
    }
)
public class InvestmentModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;

    public UUID userId;
    public Double amount;
    public Double duration;
    public Double interestRate;
    public Integer repaymentInterval;
    public LocalDate repaymentStart;

    @Column(columnDefinition = "boolean default false")
    public Boolean investmentFilesVerifiedByBackendUser;

    //08.09.2021
    @Column(columnDefinition = "boolean default false")
    public Boolean contractFinalized;

    @Column(columnDefinition = "boolean default false")
    public Boolean contractPaid;

    @Column(columnDefinition = "boolean default false")
    public Boolean contractRepaid;

    @CreationTimestamp
    public LocalDateTime t0;

    @ColumnDefault("0")
    public Integer rs;


    public static List<ObjectNode> listInvestors(String sortBy, Boolean descending, Integer page, Integer perPage, String q, Integer status) {

        Integer startIndex = 0;
        if (page > 1) {
            startIndex =  ((page-1)*perPage);
        }

        String searchString = "";
        if (!q.equals("")) {
            searchString = "AND ((UPPER(u.firstName) LIKE UPPER('%"+q+"%')) OR  (UPPER(u.lastName) LIKE UPPER('%"+q+"%')) OR (UPPER(u.email) LIKE UPPER('%"+q+"%'))) ";
        }

        String filterStatus = "";
        if (status != -1) {
            if (status == 0) {
                filterStatus = "AND i.contractfinalized is not true ";
            } else if (status == 1) {
                filterStatus = "AND i.contractfinalized is true ";
            }
        }

        StringBuilder investorQuery = new StringBuilder()
            .append("SELECT cast(i.id as varchar) as id, cast(i.userId as varchar) as userId, COALESCE(i.amount, 0) as amount, COALESCE(i.duration, 0) as duration, ")
            .append("COALESCE(i.interestRate, 0) as interestRate, COALESCE(i.repaymentInterval, 0) as repaymentInterval, COALESCE(i.repaymentStart, '1979-01-01') as repaymentStart, ")
            .append("COALESCE(i.investmentFilesVerifiedByBackendUser, false) as investmentFilesVerifiedByBackendUser, COALESCE(i.contractFinalized, false) as contractFinalized, ")
            .append("COALESCE(i.contractPaid, false) as contractPaid, COALESCE(i.contractRepaid, false) as contractRepaid, i.t0, i.rs, ")
            .append("u.firstName, u.lastName, u.email ")
            .append("FROM investment i ")
            .append("LEFT JOIN user_basic_info u ON u.id = i.userId ")
            .append("WHERE i.rs != 99 ")
            .append(searchString)
            .append(filterStatus)
            .append("ORDER BY i.t0 DESC ")
            .append("LIMIT "+perPage+" OFFSET "+startIndex+" ");

        InvestmentModel ivm = new InvestmentModel();
        EntityManager em = ivm.getEntityManager();

        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(investorQuery.toString(), Tuple.class).getResultList());

        return res;
    }
}