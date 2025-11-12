package org.bgr.model;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.hibernate.orm.panache.PanacheEntityBase;
import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.panache.common.Sort;
import org.bgr.helper.NativeQueryToJson;
import org.bgr.model.api.SolarPlantSearchModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.hibernate.annotations.ColumnDefault;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.GenericGenerator;

import javax.persistence.*;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@Entity
@Table(name="solar_plant", indexes = {
        @Index(columnList = "id", name = "ndx_solar_plant_id"),
        @Index(columnList = "title", name = "ndx_solar_plant_title"),
        @Index(columnList = "t0", name = "ndx_solar_plant_t0"),
        @Index(columnList = "tariff", name = "ndx_solar_plant_tariff")
    }
)

//statuses and fields are added constantly!!! it is difficult to maintain naming conventions
public class SolarPlantModel extends PanacheEntityBase {
    @Id
    @GeneratedValue(generator = "UUID")
    @GenericGenerator(
            name = "UUID",
            strategy = "org.hibernate.id.UUIDGenerator"
    )
    @Column(name = "id", updatable = false, nullable = false)
    public UUID id;
    public UUID tariff;

    @NotBlank(message = "title|not_blank")
    public String title;

    //@NotNull(message = "nominalPower|not_null")
    public Double nominalPower;

    //@NotNull(message = "powerProductionForecast|not_null")
    public Double powerProductionForecast;

    //@NotNull(message = "powerConsumptionForecast|not_null")
    public Double powerConsumptionForecast;

    //@NotNull(message = "consumptionValuePerKW|not_null")
    public Double consumptionValuePerKW;

    //@NotNull(message = "productionValuePerKW|not_null")
    public Double productionValuePerKW;

    //@NotNull(message = "unitPrice|not_null")
    public Double unitPrice;

    //new manual price
    /*
    public Boolean manualPrice;
    public Double calculatedPriceBeforeManualPrice;
    public UUID manualPriceByUserId;
    public String manualPriceByUserName;
    public LocalDateTime manualPriceSetAt;
     */
    
    public Double prePayment;

    //@NotNull(message = "planCost|not_null")
    public Double planCost;

    public Double planFee;
    public Double serviceFee;

    public String subventionProvider;

    //boz4
    public Double subventionCoefficient;

    //boz5
    //public Double forecastCompensation;

    public String location;
    public Double lat;
    public Double lon;

    //new and deprecated ... in an month!
    public String campaign;
    //ultra new
    public UUID campaignId;
    public Boolean buildingPermitCosts;
    public Double additionalCost;

    public Boolean joinPowerMeters;
    public Double powerConsumptionForecastMeter2;

    //08.09.2021
    @Column(columnDefinition = "boolean default false")
    public Boolean contractFinalized;

    @Column(columnDefinition = "boolean default false")
    public Boolean plantInstalled;

    @Column(columnDefinition = "boolean default false")
    public Boolean plantInUse;
    public LocalDate plantInUseDate;

    @Column(columnDefinition="TEXT")
    public String additionalCostDescription;

    @Column(columnDefinition="TEXT")
    public String documentExtraTextBlockA;

    @Column(columnDefinition="TEXT")
    public String documentExtraTextBlockB;

    @Column(columnDefinition = "boolean default false")
    public Boolean solarPlantFilesVerifiedByBackendUser;

    //why not -- ist a joy to do constant changes!!!
    @Column(columnDefinition = "boolean default false")
    public Boolean communityPlant;

    public Double unitPriceMinPrepayment;
    public Double unitPriceMaxPrepayment;
    public Double prePaymentMin;
    public Double prePaymentMax;

    //Letzte Energieabrechnung status
    public Boolean powerBillUploaded;

    //Planung Fotos status -- inspection finished
    public Boolean inspectionChecked;
    public Boolean inspectionCheckFinished;
    public LocalDateTime inspectionCheckFinishedDate;
    public String inspectionCheckFinishedMail;

    // contact/inspection properties
    //Begehungstermin
    public Boolean inspectionMailSent;

    //Kundenkontakt status
    public Boolean inspectionCheckInProgress;

    public String inspectionMailBackendUserSendTo;
    public LocalDateTime inspectionCheckDate;

    //client roof images
    public Boolean roofImagesUploadedByClient;
    public LocalDateTime roofImagesUploadedByClientDate;

    //Planungsdokument
    public Boolean planDocumentUploaded;

    //Teilnehmer Energiegemeinschaft
    @Column(columnDefinition = "boolean default false")
    public Boolean inspectionEnergyComunity;
    @Column(columnDefinition="TEXT")
    public String inspectionEnergyComunityText;

    //Warmwasserbereitung
    @Column(columnDefinition = "boolean default false")
    public Boolean inspectionWaterHeating;
    @Column(columnDefinition="TEXT")
    public String inspectionWaterHeatingText;

    //Speicher
    @Column(columnDefinition = "boolean default false")
    public Boolean inspectionStorage;
    @Column(columnDefinition="TEXT")
    public String inspectionStorageText;

    //Notstromfunktionalität
    @Column(columnDefinition = "boolean default false")
    public Boolean inspectionEmergencyPower;
    @Column(columnDefinition="TEXT")
    public String inspectionEmergencyPowerText;

    //Ladeinfrastruktur
    @Column(columnDefinition = "boolean default false")
    public Boolean inspectionChargingInfrastructure;
    @Column(columnDefinition="TEXT")
    public String inspectionChargingInfrastructureText;

    //Allgemeine Anmerkungen
    @Column(columnDefinition="TEXT")
    public String inspectionComments;

    //Anlagedaten und Prognoserechnung status
    public Boolean calculationInProgress;
    public Boolean calculationFinished;

    //Anschreiben und Prognoserechnung (upload)
    public Boolean calculationChecked;
    public Boolean calculationSentToCustomer;
    public LocalDateTime calculationSentToCustomerDate;
    public Boolean calculationMailWithRegistration;

    //kundenstorno
    public Boolean cancelByCustomer;

    //Auftragsabsicht
    public Boolean orderInterest;
    public LocalDateTime orderInterestDate;

    //Auftragsabsicht accpted
    public Boolean orderInterestAccepted;
    public LocalDateTime orderInterestAcceptedDate;

    //Contract signed
    public Boolean contractSignedAndUploaded;
    public LocalDateTime contractSignedAndUploadedDate;

    //Contract
    public Boolean contractsReviewed;
    public Boolean contractsSentToCustomer;
    public LocalDateTime contractsSentToCustomerDate;
    public String contractsSentToCustomerBackendUserSendTo;


    //Contract Signed OK
    public Boolean contractsSigned;
    public Boolean contractFilesChecked;
    public LocalDateTime contractFilesCheckedDate;

    public Boolean propertyOwnerListFinished;

    public Boolean powerBillSaved;
    public Boolean calculationSaved;

    @CreationTimestamp
    public LocalDateTime t0;

    public UUID createdById;
    public String createdByName;

    public Double subventionFrom10Kw;

    public LocalDateTime lastOpenedDate;
    public UUID lastOpenedById;
    public String lastOpenedByName;
    public String lastOpenedByUserName;

    //99 = soft delete
    @ColumnDefault("1")
    public Integer rs;

    public UUID cloneSource;

    public static List<ObjectNode> listSolarPlants(String sortBy, Boolean descending, Integer page, Integer perPage, String q, Integer status, UUID tariff, UUID campaign, Integer buyOption){

        Sort.Direction direction = Sort.Direction.Ascending;
        if (descending == true) {
            direction = Sort.Direction.Descending;
        }

        //String filter = "((UPPER(title) LIKE UPPER('%"+q+"%')) OR (UPPER(campaign) LIKE UPPER('%"+q+"%')) (UPPER(campaign) LIKE UPPER('%"+q+"%')))";

        String filterCancelByCustomer = "AND (sp.cancelByCustomer is null OR sp.cancelByCustomer = false)";

        //a little bit dumb but i am waiting for definition --- could be a single filed in table
        String filterStatus = "";
        if (status != -1) {
            if (status == 6) {
                filterStatus = " AND sp.cancelByCustomer is true ";
            } else if (status == 8) {
                filterStatus = filterCancelByCustomer + " AND sp.plantInUse is true ";
            } else if (status == 4) {
                filterStatus = filterCancelByCustomer + " AND sp.contractFilesChecked is true AND (sp.plantInUse is null OR sp.plantInUse = false)";
            } else if (status == 7) {
                filterStatus = filterCancelByCustomer + " AND sp.contractsSentToCustomer is true AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false) ";
            } else if (status == 3) {
                filterStatus = filterCancelByCustomer + " AND sp.orderInterestAccepted is true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 2) {
                filterStatus = filterCancelByCustomer + " AND sp.calculationSentToCustomer is true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.orderInterestAccepted is null OR sp.orderInterestAccepted = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 1) {
                filterStatus = filterCancelByCustomer + " AND sp.inspectionCheckFinished = true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterestAccepted is null OR sp.orderInterestAccepted = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 0) {
                filterStatus = filterCancelByCustomer + " AND sp.inspectionMailSent = true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.inspectionCheckFinished is null OR sp.inspectionCheckFinished = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterest is null OR sp.orderInterest = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 5) {
                filterStatus = filterCancelByCustomer + " AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.inspectionMailSent is null OR sp.inspectionMailSent = false) AND (sp.inspectionCheckFinished is null OR sp.inspectionCheckFinished = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterest is null OR sp.orderInterest = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            }
        }

        Integer startIndex = 0;
        Integer lastIndex = perPage - 1;

        if (page > 1) {
            startIndex =  ((page-1)*perPage);
            lastIndex = (page*perPage)-1;
        }

        UUID fakeUUID = UUID.fromString("77399e89-79f4-470b-8eec-34e13ab9d74b");

        StringBuilder plantQuery = new StringBuilder()
                .append("SELECT cast(sp.id as varchar) as id, COALESCE(sp.title, '') as title, COALESCE(sp.nominalPower, 0) as nominalPower, sp.t0, COALESCE(c.title, '') as campaignTitle, COALESCE(a.title, '') as tariffTitle, ")
                .append("COALESCE(sp.inspectionEnergyComunity, false) as inspectionEnergyComunity, ")
                .append("COALESCE(sp.inspectionWaterHeating, false) as inspectionWaterHeating, ")
                .append("COALESCE(sp.inspectionStorage, false) as inspectionStorage, ")
                .append("COALESCE(sp.inspectionEmergencyPower, false) as inspectionEmergencyPower, ")
                .append("COALESCE(sp.inspectionChargingInfrastructure, false) as inspectionChargingInfrastructure, ")
                .append("COALESCE(sp.inspectionMailSent, false) as inspectionMailSent, ")
                .append("COALESCE(sp.inspectionCheckFinished, false) as inspectionCheckFinished, ")
                .append("COALESCE(sp.calculationSentToCustomer, false) as calculationSentToCustomer, ")
                .append("COALESCE(sp.contractsSentToCustomer, false) as contractsSentToCustomer, ")
                .append("COALESCE(sp.orderInterestAccepted, false) as orderInterest, ")
                .append("COALESCE(sp.cancelByCustomer, false) as cancelByCustomer, ")
                .append("COALESCE(sp.contractFilesChecked, false) as contractFilesChecked, ")
                .append("COALESCE(sp.plantInUse, false) as plantInUse, ")
                .append("COALESCE(sp.prePayment, 0) as prePayment, ")
                .append("COALESCE(sp.unitPrice, 0) as unitPrice, ")
                .append("COALESCE(sp.planFee, 0) as planFee, ")
                .append("COALESCE(sp.serviceFee, 0) as serviceFee, ")
                .append("COALESCE(a.directBuy, false) as directBuy, ")
                .append("sp.lastOpenedDate, COALESCE(sp.lastOpenedByName, '') as lastOpenedByName ")
                .append("FROM solar_plant sp ")
                .append("LEFT JOIN campaign c ON c.id = sp.campaignid ")
                .append("LEFT JOIN admin_settings as a ON a.id = sp.tariff ")
                .append("WHERE (sp.rs IS NULL OR sp.rs < 99) ");
                //.append("AND ((UPPER(sp.title) LIKE UPPER('%"+q+"%')) OR (UPPER(c.title) LIKE UPPER('%"+q+"%')) OR (UPPER(a.title) LIKE UPPER('%"+q+"%'))) ")

                if (!tariff.equals(fakeUUID)) {
                    plantQuery.append("AND a.id = '"+tariff+"' ");
                }

                if (!campaign.equals(fakeUUID)) {
                    plantQuery.append("AND c.id = '"+campaign+"' ");
                }

                if (buyOption == 1) {
                    plantQuery.append("AND a.directBuy = 'true' ");
                } else if (buyOption == 0) {
                    plantQuery.append("AND a.directBuy != 'true' ");
                }

        plantQuery
            .append("AND (UPPER(sp.title) LIKE UPPER('%"+q+"%')) ")
            .append(filterStatus)
            .append("ORDER BY sp.lastOpenedDate DESC ")
            .append("LIMIT "+perPage+" OFFSET "+startIndex+" ");

        SolarPlantModel spm = new SolarPlantModel();
        EntityManager em = spm.getEntityManager();

        //System.out.println(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());
        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());

        return res;
    }

    public static long countSolarPlants(String q, Integer status, UUID tariff, UUID campaign, Integer buyOption){
        String filterCancelByCustomer = "AND (sp.cancelByCustomer is null OR sp.cancelByCustomer = false)";

        //a little bit dumb but i am waiting for definition --- could be a single filed in table
        String filterStatus = "";
        if (status != -1) {
            if (status == 6) {
                filterStatus = " AND sp.cancelByCustomer is true ";
            } else if (status == 8) {
                filterStatus = filterCancelByCustomer + " AND sp.plantInUse is true ";
            } else if (status == 4) {
                filterStatus = filterCancelByCustomer + " AND sp.contractFilesChecked is true AND (sp.plantInUse is null OR sp.plantInUse = false)";
            } else if (status == 7) {
                filterStatus = filterCancelByCustomer + " AND sp.contractsSentToCustomer is true AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false) ";
            } else if (status == 3) {
                filterStatus = filterCancelByCustomer + " AND sp.orderInterestAccepted is true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 2) {
                filterStatus = filterCancelByCustomer + " AND sp.calculationSentToCustomer is true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.orderInterestAccepted is null OR sp.orderInterestAccepted = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 1) {
                filterStatus = filterCancelByCustomer + " AND sp.inspectionCheckFinished = true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterestAccepted is null OR sp.orderInterestAccepted = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 0) {
                filterStatus = filterCancelByCustomer + " AND sp.inspectionMailSent = true AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.inspectionCheckFinished is null OR sp.inspectionCheckFinished = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterest is null OR sp.orderInterest = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            } else if (status == 5) {
                filterStatus = filterCancelByCustomer + " AND ((sp.contractsSentToCustomer is null OR sp.contractsSentToCustomer = false) AND (sp.inspectionMailSent is null OR sp.inspectionMailSent = false) AND (sp.inspectionCheckFinished is null OR sp.inspectionCheckFinished = false) AND (sp.calculationSentToCustomer is null OR sp.calculationSentToCustomer = false) AND (sp.orderInterest is null OR sp.orderInterest = false) AND (sp.contractFilesChecked is null OR sp.contractFilesChecked = false)) ";
            }
        }

        UUID fakeUUID = UUID.fromString("77399e89-79f4-470b-8eec-34e13ab9d74b");

        StringBuilder plantQuery = new StringBuilder()
                .append("SELECT COUNT(sp.id) as nr ")
                .append("FROM solar_plant sp ")
                .append("LEFT JOIN campaign c ON c.id = sp.campaignid ")
                .append("LEFT JOIN admin_settings as a ON a.id = sp.tariff ")
                .append("WHERE (sp.rs IS NULL OR sp.rs < 99) ");

        if (!tariff.equals(fakeUUID)) {
            plantQuery.append("AND a.id = '"+tariff+"' ");
        }

        if (!campaign.equals(fakeUUID)) {
            plantQuery.append("AND c.id = '"+campaign+"' ");
        }

        if (buyOption == 1) {
            plantQuery.append("AND a.directBuy = 'true' ");
        } else if (buyOption == 0) {
            plantQuery.append("AND a.directBuy != 'true' ");
        }

        plantQuery
                .append("AND (UPPER(sp.title) LIKE UPPER('%"+q+"%')) ")
                .append(filterStatus);

        SolarPlantModel spm = new SolarPlantModel();
        EntityManager em = spm.getEntityManager();
        return Long.parseLong(em.createNativeQuery(plantQuery.toString()).getResultList().get(0).toString());
    }

    //
    public static List<ObjectNode> listSolarPlantRepayment(String sortBy, Boolean descending, Integer page, Integer perPage, String q, Integer status,
                                                           Integer calculationYear, Integer calculationYearPeriod){
        Integer startIndex = 0;
        if (page > 1) {
            startIndex =  ((page-1)*perPage);
        }

        String filterStatus = " AND sp.contractFilesChecked is true AND sp.plantInUse is true ";

        String filterRepaymentPeriod = "";
        if (calculationYear == -1) {
            filterRepaymentPeriod += "%";
        } else {
            filterRepaymentPeriod += calculationYear;
        }

        filterRepaymentPeriod += "/";

        if (calculationYearPeriod == -1) {
            filterRepaymentPeriod += "%";
        } else {
            filterRepaymentPeriod += calculationYearPeriod;
        }

        if (!filterRepaymentPeriod.equals("%/%")) {
            filterStatus += "AND (sprlu.repaymentperiod LIKE '"+filterRepaymentPeriod+"' or sprl.repaymentperiod LIKE '"+filterRepaymentPeriod+"') ";
        }

        StringBuilder plantQuery = new StringBuilder()
            .append("SELECT cast(sp.id as varchar) as id, COALESCE(sp.title, '') as title, COALESCE(sp.nominalPower, 0) as nominalPower, ")
            .append("sp.unitPrice, sp.contractFilesCheckedDate, ")
            .append("COALESCE(SUM(sprl.amount),0) as repaid, ")
            .append("COALESCE(SUM(sprlu.amount),0) as open ")
            .append("FROM solar_plant sp ")
            .append("LEFT JOIN solar_plant_repayment_log sprl on (sprl.plantid = sp.id AND sprl.paymentverified IS TRUE) ")
            .append("LEFT JOIN solar_plant_repayment_log sprlu on (sprlu.plantid = sp.id AND sprlu.paymentverified IS NOT TRUE) ");

        plantQuery
            .append("WHERE (UPPER(sp.title) LIKE UPPER('%"+q+"%')) ")
            .append(filterStatus)
            .append("GROUP BY sp.id ");

        //tmp only
        if (status == 1) {
            plantQuery.append("HAVING SUM(sprlu.amount) > 0 ");
        } else if (status == 2) {
            plantQuery.append("HAVING SUM(sprlu.amount) <= 0 ");
        }

        plantQuery.append("ORDER BY sp.contractFilesCheckedDate DESC ")
            .append("LIMIT "+perPage+" OFFSET "+startIndex+" ");


        SolarPlantModel spm = new SolarPlantModel();
        EntityManager em = spm.getEntityManager();

        //System.out.println(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());
        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());

        return res;
    }
}
