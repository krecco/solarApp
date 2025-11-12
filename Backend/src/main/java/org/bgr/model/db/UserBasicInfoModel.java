package org.bgr.model.db;

import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Sort;
import org.apache.commons.lang3.StringUtils;
import org.bgr.model.UserAddressModel;
import org.bgr.model.api.UserListModel;
import org.hibernate.annotations.*;

import javax.persistence.*;
import javax.persistence.Entity;
import javax.persistence.Index;
import javax.persistence.Table;
import javax.validation.constraints.Email;
import javax.validation.constraints.NotBlank;
import java.time.LocalDateTime;
import java.util.*;

@Entity
@Table(name="user_basic_info", indexes = {
        @Index(columnList = "id", name = "ndx_user_basic_info_id"),
        @Index(columnList = "userId", name = "ndx_user_basic_info_userId"),
        @Index(columnList = "t0", name = "ndx_user_basic_info_t0")
    }
)
public class UserBasicInfoModel extends PanacheEntityBase {
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

    @Email(message = "email|not_valid")
    @NotBlank(message = "email|not_blank")
    public String email;

    @NotBlank(message = "firstName|not_blank")
    public String firstName;

    @NotBlank(message = "lastName|not_blank")
    public String lastName;

    public String phoneNr;

    @Type(type = "org.hibernate.type.IntegerType")
    public Integer gender;

    public String titlePrefix;
    public String titleSuffix;

    @Column(columnDefinition = "boolean default false")
    public Boolean isBusiness;

    //to be deprecated
    @Column(columnDefinition = "boolean default false")
    public Boolean isInvestor;

    //to be deprecated
    @Column(columnDefinition = "boolean default false")
    public Boolean isPlantOwner;

    @ColumnDefault("0")
    public Integer customerType;

    @Column(columnDefinition="TEXT")
    public String documentExtraTextBlockA;

    @Column(columnDefinition="TEXT")
    public String documentExtraTextBlockB;

    @Column(columnDefinition = "boolean default false")
    public Boolean userFilesVerifiedByBackendUser;

    @Column(columnDefinition = "boolean default false")
    public Boolean userRegisterMailSent;

    @CreationTimestamp
    public LocalDateTime t0;

    //99 = soft delete
    @ColumnDefault("0")
    public Integer rs;

    @Column(columnDefinition="serial")
    @Generated(GenerationTime.INSERT)
    public Integer customerNo;

    public static List<UserListModel> listUsers(String sortBy, Boolean descending, Integer page, Integer perPage, String q, Integer customerType){
        PanacheQuery<UserBasicInfoModel> userList = null;

        Sort.Direction direction = Sort.Direction.Ascending;
        if (descending == true) {
            direction = Sort.Direction.Descending;
        }

        StringBuilder filterQuery = new StringBuilder();
        if (customerType >= 0){
            if ((customerType == 0) || (customerType == 30)) {
                filterQuery.append("customerType = "+customerType+" ");
            } else if (customerType == 10) {
                filterQuery.append("customerType IN (10,30) ");
            } else if (customerType == 20) {
                filterQuery.append("customerType IN (20,30) ");
            }
        }

        if (!q.equals("")) {
            String userIds = fullTextSearch(q);
            if (!userIds.equals("")) {
                if  (filterQuery.length() > 0) {
                    filterQuery.append(" AND ");
                }

                userList = UserBasicInfoModel.find(filterQuery+"id IN ("+userIds+") AND (rs IS NULL OR rs < 99)",Sort.by(sortBy, direction));
            }
        } else {
            //userList = UserBasicInfoModel.findAll(Sort.by(sortBy, direction));

            if  (filterQuery.length() > 0) {
                filterQuery.append(" AND ");
                //filterQuery.append(" WHERE ");
            }

            userList = UserBasicInfoModel.find(filterQuery.toString()+"(rs IS NULL OR rs < 99)",Sort.by(sortBy, direction));
            //userList = UserBasicInfoModel.find("select distinct u, a.street from UserBasicInfoModel u left join UserAddressModel a ON a.userId = u.userId"+filterQuery.toString()+" WHERE (u.rs IS NULL OR u.rs < 99) ",Sort.by("u."+sortBy, direction));
        }

        if (userList != null) {

            Integer startIndex = 0;
            Integer lastIndex = perPage - 1;

            if (page > 1) {
                startIndex =  ((page-1)*perPage);
                lastIndex = (page*perPage)-1;
            }
            userList.range(startIndex, lastIndex);

            //quick addon -- will leave this wit sub-query for now as it is paginated
            List<UserListModel> userListResp = new ArrayList<>();
            for (UserBasicInfoModel user : userList.list())
            {
                UserListModel userResp = new UserListModel();

                userResp.id = user.id;
                userResp.userId = user.userId;
                userResp.customerNo = user.customerNo;
                userResp.email = user.email;
                userResp.firstName = user.firstName;
                userResp.lastName = user.lastName;
                userResp.customerType = user.customerType;
                userResp.gender = user.gender;
                userResp.isBusiness = user.isBusiness;
                userResp.isInvestor = user.isInvestor;
                userResp.isPlantOwner = user.isPlantOwner;
                userResp.phoneNr = user.phoneNr;
                userResp.t0 = user.t0;
                userResp.titlePrefix = user.titlePrefix;
                userResp.titleSuffix = user.titleSuffix;

                UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
                userResp.street = address.street;
                userResp.postNr = address.postNr;
                userResp.city = address.city;

                userListResp.add(userResp);
            }

            return userListResp;
        } else {
            return null;
        }
    }

    public static long countUsers(String q, Integer customerType){
        long userCount = 0;

        StringBuilder filterQuery = new StringBuilder();
        if (customerType >= 0){
            if ((customerType == 0) || (customerType == 30)) {
                filterQuery.append("customerType = "+customerType+" ");
            } else if (customerType == 10) {
                filterQuery.append("customerType IN (10,30) ");
            } else if (customerType == 20) {
                filterQuery.append("customerType IN (20,30) ");
            }
        }

        if (!q.equals("")) {
            String userIds = fullTextSearch(q);
            if (!userIds.equals("")) {
                if  (filterQuery.length() > 0) {
                    filterQuery.append(" AND ");
                }

                userCount = UserBasicInfoModel.count(filterQuery+"id IN ("+userIds+") AND (rs IS NULL OR rs < 99)");
            }
        } else {
            if  (filterQuery.length() > 0) {
                filterQuery.append(" AND ");
            }

            userCount = UserBasicInfoModel.count(filterQuery.toString()+"(rs IS NULL OR rs < 99)");
        }

        return userCount;
    }

    public static String fullTextSearch(String token) {
        StringBuilder queryToken = new StringBuilder();
        String split[]= StringUtils.split(token);

        for (int i = 0; i < split.length; i++) {
            queryToken.append(split[i]+":*");
            if (i < split.length-1 ) {
                queryToken.append(" & ");
            }
        }

        String fullTextQuery = new StringBuilder()
                .append("SELECT cast(userID as varchar) ")
                .append("FROM ( ")
                .append("SELECT ubi.id as userId, ")
                .append("to_tsvector('simple', ubi.firstname) || ")
                .append("to_tsvector('simple', ubi.lastname) || ")
                .append("to_tsvector('simple', ua.street) || ")
                .append("to_tsvector('simple', ua.city) || ")
                .append("to_tsvector('simple', ua.postnr) || ")
                .append("to_tsvector('simple', ubi.email) as document ")
                .append("FROM user_basic_info ubi ")
                .append("LEFT JOIN user_address ua ON ubi.userid = ua.userid ")
                .append(") ")
                .append("as p_search ")
                .append("WHERE p_search.document @@ to_tsquery('simple', '"+queryToken+"'); ")
                .toString();

        UserBasicInfoModel ubm = new UserBasicInfoModel();
        EntityManager em = ubm.getEntityManager();

        String userIds = "";
        List<String>  results = em.createNativeQuery(fullTextQuery).getResultList();

        if (!results.isEmpty()) {
            for (String result : results) {
                userIds += "'"+result+"',";
            }
            userIds = userIds.substring(0, userIds.length() - 1);
        }
        return userIds;
    }
}
