package org.bgr.resource;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;
import com.fasterxml.jackson.dataformat.csv.CsvMapper;
import com.fasterxml.jackson.dataformat.csv.CsvSchema;
import io.quarkus.panache.common.Sort;
import io.quarkus.qute.Template;
import io.quarkus.security.identity.SecurityIdentity;
import org.apache.http.HttpEntity;
import org.apache.http.client.methods.CloseableHttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.CloseableHttpClient;
import org.apache.http.impl.client.HttpClients;
import org.bgr.helper.*;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.SolarPlantPowerBill;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.io.File;
import java.io.FileOutputStream;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.*;

import org.bgr.model.UserAddressModel;
import org.bgr.model.api.ExtrasTableModel;
import org.bgr.model.api.FrontendMsgToBackoffice;
import org.bgr.model.api.PlantPreviewModel;
import org.bgr.model.api.StatusModel;
import org.bgr.model.db.*;
import org.bgr.service.*;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.eclipse.microprofile.jwt.JsonWebToken;

import io.quickchart.QuickChart;

@ApplicationScoped
@Path("solar-plant")
public class SolarPlantResource {

    public class GenerateFileResponse {
        public String fileName;
    }

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @ConfigProperty(name = "app.folder.documents")
    String documentFolder;

    @ConfigProperty(name = "app.chartServiceUrl")
    String chartServiceUrl;

    @ConfigProperty(name = "app.webInfo.notificationMail")
    String notificationMail;

    @Inject
    Template forecast_calculation;

    @Inject
    Template forecast_calculation_sub_header;

    @Inject
    HtmlDocumentGenerator htmlDocumentGenerator;

    @Inject
    FileHelper fileHelper;

    @Inject
    MailService mailService;

    @Inject
    UserService userService;

    @Inject
    ActivityService activityService;

    @Inject
    StatusService statusService;

    @Inject
    CalendarEventMail calendarEventMail;

    @Inject
    SolarPlantCloneService solarPlantCloneService;

    //OK 3
    @POST
    @RolesAllowed({"manager", "admin"})
    //@Path("add")
    @Transactional
    public SolarPlantModel addSolarPlant(SolarPlantModel plant) {

        //why not - lets change it again!
        //tariff should not be selected at creating plant anymore!!!
        /*
        UUID tariffId = plant.tariff;
        SettingsModel tariff = SettingsModel.findById(tariffId);

        //calculate service fee
        Double serviceFee = tariff.serviceFee * plant.nominalPower;
        if (serviceFee < tariff.serviceFeeMin) {
            serviceFee = (double) tariff.serviceFeeMin;
        } else if (serviceFee > tariff.serviceFeeMax) {
            serviceFee = (double) tariff.serviceFeeMax;
        }

        plant.serviceFee = serviceFee;

        //calulate plan fee
        Double planFee = tariff.planningFlatRate * plant.nominalPower;
        if (planFee < tariff.planningFlatRateMin) {
            planFee = (double) tariff.planningFlatRateMin;
        } else if (planFee > tariff.planningFlatRateMax) {
            planFee = (double) tariff.planningFlatRateMax;
        }

        plant.planFee = planFee;

        //subventionCoefficient
        Double subventionCoefficient = tariff.subventionTo10Kw;
        if (plant.nominalPower > 10) {
            subventionCoefficient = tariff.subventionFrom10Kw;
        }
        plant.subventionCoefficient = subventionCoefficient;

        Double maxSubvention = 0.0;
        if (plant.nominalPower > 10) {
            maxSubvention = 10 * tariff.subventionTo10Kw + ((plant.nominalPower - 10) * tariff.subventionFrom10Kw);
        } else {
            maxSubvention = plant.nominalPower * tariff.subventionTo10Kw;
        }

        //calculate prepayment
        Double minPrePayment = Math.ceil(planFee / 50) * 50;
        Double maxPrePayment = Math.ceil((maxSubvention / 0.35) / 50) * 50;

        Double defaultPrePayment = Math.ceil(((minPrePayment+maxPrePayment)/2)/50) *50;
        plant.prePayment = defaultPrePayment;
         */

        //default
        plant.subventionProvider = "";

        //hardcoded in version one!
        //plant.tariff = UUID.fromString("d8943975-ccee-4cf8-92dc-f93b2f0eddc3");

        plant.createdById = UUID.fromString(token.getSubject());
        plant.createdByName = token.getClaim("name").toString();

        plant.persist();

        //important! same code in solar plant clone service -- refracture code when you have the time!

        //only download
        //A_C-solar.family Prognoserechnung
        FileContainerModel fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 21;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 3;
        fc.uploadOnly = false;
        fc.downloadOnly = true;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = true;
        fc.persist();

        //only backend upload
        //A_B-solar.family Projektierung Teil1
        //A_B-solar.family Projektierung Teil2
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 22;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 2;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = true;
        fc.backendOnly = false;
        fc.noStatusUpdate = true;
        fc.persist();

        //A_C-solar.family galerie
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 26;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 8;
        fc.uploadOnly = true;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = true;
        fc.persist();

        //A_A-solar.family Anschreiben
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 23;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 1;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_D-solar.family Vertrag Energieeinsparung
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 24;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 4;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_E-solar.family Vertrag Verrechnungsblatt
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 25;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 5;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_F-solar.family Vollmacht Abwicklung
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 27;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 6;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();


        //Backend only files
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 28;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 9;
        fc.uploadOnly = true;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = true;
        fc.noStatusUpdate = true;
        fc.persist();

        //A_G-solar.family Vollmacht Energieabrechnung
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 29;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 7;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_G-solar.family Vollmacht Netzbetreiber
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 291;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 8;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_G-solar.family SEPA
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 201;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 9;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_G-solar.family Vollmacht Netzbetreiber
        fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 20;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 1;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Dach
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 30;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 1;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Wechselrichter
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 31;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 2;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Kabelverlegung
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 32;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 3;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Einspeisepunkt
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 33;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 4;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();


        // Warmwasserbereitung
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 34;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 5;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Ladeinfrastruktur
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 35;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 6;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Zähler
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 36;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 7;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        // Speicher
        fc = new FileContainerModel();
        fc.contextType = 3;
        fc.type = 37;
        fc.contextId = plant.id;
        fc.rs = 1;
        fc.sortOrder = 8;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //add extras to plant
        List<ExtrasModel> extrasList = ExtrasModel.find("rs=?1 and active=?2", Sort.by("active", Sort.Direction.Descending).and("title", Sort.Direction.Ascending),1,true).list();
        for (ExtrasModel et : extrasList) {

            SolarPlantExtrasModel spe =  new SolarPlantExtrasModel();
            spe.extrasId = et.id;
            spe.plantId = plant.id;
            spe.title = et.title;
            spe.price = et.price;
            spe.qt = 1.0;
            spe.active = false;
            spe.persist();
        }

        //power bill
        UserBasicInfoModel ubm = new UserBasicInfoModel();
        EntityManager em = ubm.getEntityManager();

        String q = new StringBuilder()
                .append("INSERT INTO solar_plant_power_bill(id, plantId, rs) ")
                .append("VALUES ('"+UUID.randomUUID().toString()+"','"+plant.id+"', 1) ")
                .toString();

        em.createNativeQuery(q).executeUpdate();
        return plant;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("clone-plant/{plantId}")
    public ResultCommonObject clonePlant(@PathParam("plantId") UUID plantId) {
        try {

            return solarPlantCloneService.clonePlant(plantId);

            //return new ResultCommonObject(200, "OK");
        } catch (Exception e) {
            System.out.println(e.toString());
            return new ResultCommonObject(400, "Error cloning solar plant");
        }
    }

    //to be deprecated
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("clone/{plantId}/{userId}")
    @Transactional
    public ResultHelper clone(@PathParam("plantId") UUID plantId, @PathParam("userId") UUID userId) {
        try {
            SolarPlantModel existingPlant = SolarPlantModel.findById(plantId);

            SolarPlantModel newPlant = new SolarPlantModel();
            newPlant.title = existingPlant.title + " Clone";
            newPlant.nominalPower = existingPlant.nominalPower;

            SolarPlantModel clonePlant = addSolarPlant(newPlant);
            clonePlant.solarPlantFilesVerifiedByBackendUser = false;
            clonePlant.lat = null;
            clonePlant.lon = null;
            clonePlant.location = "";
            clonePlant.planFee = existingPlant.planFee;
            clonePlant.prePayment = existingPlant.prePayment;
            clonePlant.subventionProvider = existingPlant.subventionProvider;
            clonePlant.subventionCoefficient = existingPlant.subventionCoefficient;
            clonePlant.serviceFee = existingPlant.serviceFee;
            clonePlant.tariff = existingPlant.tariff;
            clonePlant.unitPrice = existingPlant.unitPrice;
            clonePlant.powerProductionForecast = existingPlant.powerProductionForecast;
            clonePlant.powerConsumptionForecast = existingPlant.powerConsumptionForecast;
            clonePlant.consumptionValuePerKW = existingPlant.consumptionValuePerKW;
            clonePlant.productionValuePerKW = existingPlant.productionValuePerKW;
            clonePlant.planCost = existingPlant.planCost;

            clonePlant.persist();
            addUserToProject(userId,clonePlant.id);

            return new SolarPlantResult(200, clonePlant);
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(500, "Error adding new solar plant"));
        }
    }

    //OK 3
    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("add/{userId}")
    @Transactional
    public ResultHelper adSolarPlantWithUser(@PathParam("userId") UUID userId, SolarPlantModel plant) {

        try {
            SolarPlantModel newPlant = addSolarPlant(plant);
            addUserToProject(userId,newPlant.id);

            return new SolarPlantResult(200, newPlant);
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(500, "Error adding new solar plant"));
        }
    }

    //OK 3
    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("edit")
    @Transactional
    public ResultCommonObject editSolarPlant(SolarPlantModel req) {

        try {
            SolarPlantModel plant = SolarPlantModel.findById(req.id);

            if (plant.solarPlantFilesVerifiedByBackendUser == null) {
                plant.solarPlantFilesVerifiedByBackendUser = false;
            }

            if (plant.solarPlantFilesVerifiedByBackendUser == true) {
                return new ResultCommonObject(403, "Project finished, cant change values!");
            }

            plant.title = req.title;
            plant.nominalPower = req.nominalPower;
            plant.powerProductionForecast = req.powerProductionForecast;
            plant.powerConsumptionForecast = req.powerConsumptionForecast;
            plant.consumptionValuePerKW = req.consumptionValuePerKW;

            plant.productionValuePerKW = req.productionValuePerKW;

            plant.unitPrice = req.unitPrice;
            plant.planCost = req.planCost;

            plant.prePayment = req.prePayment;

            plant.planFee = req.planFee;
            plant.serviceFee = req.serviceFee;

            plant.lat = req.lat;
            plant.lon = req.lon;
            plant.location = req.location;
            plant.subventionProvider = req.subventionProvider;
            plant.tariff = req.tariff;

            plant.subventionCoefficient = req.subventionCoefficient;
            //plant.forecastCompensation = req.forecastCompensation;

            plant.campaign = req.campaign;
            plant.additionalCost = req.additionalCost;
            plant.additionalCostDescription = req.additionalCostDescription;
            plant.documentExtraTextBlockA = req.documentExtraTextBlockA;
            plant.documentExtraTextBlockB = req.documentExtraTextBlockB;
            plant.buildingPermitCosts = req.buildingPermitCosts;

            plant.joinPowerMeters = req.joinPowerMeters;
            plant.powerConsumptionForecastMeter2 = req.powerConsumptionForecastMeter2;

            plant.unitPriceMinPrepayment = req.unitPriceMinPrepayment;
            plant.unitPriceMaxPrepayment = req.unitPriceMaxPrepayment;

            plant.prePaymentMin = req.prePaymentMin;
            plant.prePaymentMax = req.prePaymentMax;
            plant.communityPlant = req.communityPlant;

            plant.campaignId = req.campaignId;
            plant.subventionFrom10Kw = req.subventionFrom10Kw;

            //plant.calculationInProgress = true;
            plant.calculationSaved = true;

            plant.createdByName = req.createdByName;

            plant.persist();

            return new ResultCommonObject(200, "OK");

        } catch (Exception e) {
            System.out.println(e);
            return new ResultCommonObject(500, "Error Editing Solar Plant");
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("/{plantId}")
    public ResultHelper getPlantInfo(@PathParam("plantId") UUID plantId) {
        if (securityIdentity.getRoles().contains("user")) {

            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        return new SolarPlantResult(200, plant);
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("delete-plant/{plantId}")
    @Transactional
    public ResultCommonObject deletePlant(@PathParam("plantId") UUID plantId) {
        try {
            SolarPlantModel plant = SolarPlantModel.findById(plantId);
            ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            plant.rs = 99;
            plant.persist();

            ActivityModel activity = new ActivityModel();
            activity.contentType = "event-plant";
            activity.contentId = plant.id;
            activity.content = plant.title;
            activity.userId = user.id;
            activity.rs = 0;
            activity.title = "delete_plant";
            activityService.processActivity(activity, "event-plant");

            return new ResultCommonObject(200, "OK");
        } catch (Exception e) {
            return new ResultCommonObject(500, "FAIL");
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-gallery/{contextId}")
    public SolarFileResult getGallery(@PathParam("contextId") UUID contextId) {
        FileContainerModel fc = FileContainerModel.find("contextId = ?1 and contextType = 2 and type = 26", contextId).firstResult();
        List<FileModel> fm = FileModel.find("idFileContainer = ?1", Sort.by("t0").ascending() ,fc.id).list();

        return new SolarFileResult(200, fm);
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-gallery-plan/{contextId}")
    public SolarFileResult getGallerPlanty(@PathParam("contextId") UUID contextId) {
        List<FileContainerModel> fcList = FileContainerModel.find("contextId = ?1 and contextType = 3", contextId).list();

        String fcIds = "";
        List<UUID> fcIdsUUID = new ArrayList();;
        if (!fcList.isEmpty()) {
            for (FileContainerModel fc : fcList) {
                fcIds += "'"+fc.id.toString()+"',";
                fcIdsUUID.add(fc.id);
            }
            fcIds = fcIds.substring(0, fcIds.length() - 1);
        }

        List<FileModel> fm = FileModel.find("idFileContainer in (?1) AND fileContentType like 'image%' ", Sort.by("t0").ascending() ,fcIdsUUID).list();

        //return fcIds;
        return new SolarFileResult(200, fm);
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("list")
    @Transactional
    public SolarPlantResult getSolarPlantList(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy,
            @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("status") Integer status, @QueryParam("tariff") UUID tariff,
            @QueryParam("campaign") UUID campaign, @QueryParam("buyOption") Integer buyOption) {

        return new SolarPlantResult(200,
                SolarPlantModel.countSolarPlants(q, status, tariff, campaign, buyOption),
                SolarPlantModel.listSolarPlants(sortBy, descending, page, perPage, q, status, tariff, campaign, buyOption));
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("list-csv")
    @Transactional
    public Response getSolarPlantListCsv(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy,
         @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("status") Integer status, @QueryParam("tariff") UUID tariff,
         @QueryParam("campaign") UUID campaign, @QueryParam("buyOption") Integer buyOption) {
        // page=0, perPage=1000000 --

        List<ObjectNode> res = SolarPlantModel.listSolarPlants(sortBy, descending, 0, 1000000, q,
                status, tariff, campaign, buyOption);

        //System.out.println(res);

        for (ObjectNode obj : res) {
            obj.put("prepayment", String.format(Locale.GERMAN, "%1$,.2f", obj.get("prepayment").asDouble()));
            obj.put("unitprice", String.format(Locale.GERMAN, "%1$,.2f", obj.get("unitprice").asDouble()));
            obj.put("planfee", String.format(Locale.GERMAN, "%1$,.2f", obj.get("planfee").asDouble()));
            obj.put("servicefee", String.format(Locale.GERMAN, "%1$,.2f", obj.get("servicefee").asDouble()));
            obj.put("nominalpower", String.format(Locale.GERMAN, "%1$,.2f", obj.get("nominalpower").asDouble()));

            String t0Formated = LocalDateTime.parse(obj.get("t0").asText().split("\\.", 2)[0], DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")).format(DateTimeFormatter.ofPattern("dd.MM.yyyy hh:mm:ss"));
            obj.put("t0", t0Formated);

            obj.remove("lastopeneddate");
            obj.remove("lastopenedbyname");
        }

        try {
            JsonNode jsonTree = new ObjectMapper().readTree(res.toString());
            JsonNode firstObject = jsonTree.elements().next();

            CsvSchema.Builder csvSchemaBuilder = CsvSchema.builder();
            firstObject.fieldNames().forEachRemaining(fieldName -> {csvSchemaBuilder.addColumn(fieldName);} );
            CsvSchema csvSchema = csvSchemaBuilder.build().withoutHeader().withColumnSeparator(';');

            String csv = "id;titel;engpassleistung;datum;kampagne;tarif;energiegemeinschaft;warmwasserbereitung;speicher;notstromfunktionalitat;ladeinfrastruktur;" +
                    "begehungstermin_vereinbart;begehungstermin_durchgefuhrt;planungsunterlagen_erstellt;vertragsunterlagen_erstellt;auftragsabsicht;kundenstorno;" +
                    "auftrag_erhalten;im_betrieb;anzahlung;preis;" +
                    "planungspauschale;servicepauschale;direktkauf";
            csv += "\n";

            CsvMapper csvMapper = new CsvMapper();
            csv += csvMapper.writerFor(JsonNode.class)
                    .with(csvSchema)
                    .writeValueAsString(jsonTree);

            return Response.ok(csv, MediaType.APPLICATION_OCTET_STREAM)
                    .header("Content-Disposition", "attachment; filename=export.csv" )
                    .build();


            //Response.ResponseBuilder response = Response.ok(Base64.getEncoder().encodeToString(FileUtils.readFileToByteArray(fileDownload)), file.fileContentType);

        } catch (JsonProcessingException e) {
            //return Response.status(200, e.toString()).build();
            return Response.status(500).build();
        }
    }

    //OK 3 -- code has exploded due to constant changes
    //this is one of the most fu**ed up dev I have ever done, no plan, just adding, removing, switching, .... I have stopped counting versions!
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("power-forecast-calculation/{plantId}/{userId}/{generate}/{title}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public Response powerForecastCalculation(@PathParam("plantId") UUID plantId, @PathParam("userId") UUID userId, @PathParam("generate") Boolean generate, @PathParam("title") String title) {
        String folder = documentFolder+"generated/forecast_calculation/";
        String chartFolder = documentFolder+"generated/chartImages/";

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        SettingsModel tariff = SettingsModel.findById(plant.tariff);
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId",plantId).firstResult();
        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

        List<SolarPlantExtrasModel> extrasList = SolarPlantExtrasModel.find("plantId=?1 AND active=?2", Sort.by("title", Sort.Direction.Ascending),plantId, true).list();
        Double extrasSum = 0.0;
        List<ExtrasTableModel> extras = new ArrayList<>();
        for (SolarPlantExtrasModel et : extrasList) {
            ExtrasTableModel obj = new ExtrasTableModel();
            obj.id = et.id;
            obj.included = "nein";
            if (et.active == true) {
                obj.included = "ja";
                extrasSum += (et.price);
            }
            obj.title = et.title;
            obj.price = GermanNumberParser.getGermanNumberFormat(et.price*1.2,2);
            extras.add(obj);
        }

        //set null to zero!
        if (powerBill != null) {
            if (Objects.isNull(powerBill.consumptionValue2)) {
                powerBill.consumptionValue2 = 0.0;
            }

            if (Objects.isNull(powerBill.consumption2)) {
                powerBill.consumption2 = 0.0;
            }

            if (Objects.isNull(powerBill.contract2)) {
                powerBill.contract2 = "";
            }
        }

        if (plant != null) {
            if (Objects.isNull(plant.joinPowerMeters)) {
                plant.joinPowerMeters = false;
            }

            if (Objects.isNull(plant.communityPlant)) {
                plant.communityPlant = false;
            }

            if (Objects.isNull(plant.powerConsumptionForecastMeter2)) {
                plant.powerConsumptionForecastMeter2 = 0.0;
            }
        }

        /*document type
            1 - one meter
            2 - two powerMeters
            3 - two powerMeters joined
            4 - community
        */
        Integer calculationType = 1;
        Integer nrPowerMeters = 1;
        String calculationTitle = "Ohne";
        if (powerBill.consumption2 > 0.0) {
            calculationType = 2;
            nrPowerMeters = 2;
            calculationTitle = "Ohne";
        }
        if (plant.joinPowerMeters == true) {
            calculationType = 3;
            nrPowerMeters = 2;
            calculationTitle = "Zählerzusammenlegung";
        }
        if (plant.communityPlant == true) {
            calculationType = 4;
            nrPowerMeters = 2;
            calculationTitle = "Gemeinschaftsanlage";
        }
        //fake status for now
        //calculationType = 4;
        //calculationTitle = "Gemeinschaftsanlage";

        //override calculationTitle
        calculationTitle = user.lastName+", "+address.street;

        //new
        Double consumptionVal = powerBill.consumptionValue;
        Double consumption = powerBill.consumption;
        if (nrPowerMeters == 2) {
            consumptionVal += powerBill.consumptionValue2;
            consumption += powerBill.consumption2;
        }

        String consumptionValue = GermanNumberParser.getGermanNumberFormat(consumptionVal,2);
        String consumptionInKwh = GermanNumberParser.getGermanNumberFormat(consumption,2);
        //old

        Double coefficient1 = powerBill.consumptionValue / powerBill.consumption;
        Double coefficient2 = powerBill.consumptionValue2 / powerBill.consumption2;

        Double consumptionSubtract1 =  powerBill.consumption - plant.powerConsumptionForecast;
        if (calculationType == 3) {
            consumptionSubtract1 = powerBill.consumption + powerBill.consumption2 - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }
        if (calculationType == 4) {
            consumptionSubtract1 = powerBill.consumption - plant.powerConsumptionForecast;
        }

        Double consumptionKwExternTemp = consumptionSubtract1 + powerBill.consumption2;

        Double consumptionSubtract2 = powerBill.consumption2;
        if (calculationType == 3) {
            consumptionSubtract2 = powerBill.consumption2 - plant.powerConsumptionForecastMeter2;
        }
        if (calculationType == 4) {
            consumptionSubtract2 = powerBill.consumption2 - plant.powerConsumptionForecastMeter2;
        }

        Double consumptionSubtract1Value = consumptionSubtract1 * coefficient1;
        Double consumptionSubtract2Value = 0.0;

        if ((consumptionSubtract2 * coefficient2) > 0) {
            consumptionSubtract2Value = consumptionSubtract2 * coefficient2;
        }

        Double consumptionExternTemp = 0.0;
        if ((powerBill.consumption2 * coefficient2) > 0) {
            consumptionExternTemp = powerBill.consumption2 * coefficient2;
        }

        Double consumptionExternSumTemp = consumptionSubtract1Value + consumptionExternTemp;

        Double surPlusPowerExtern = plant.powerProductionForecast - plant.powerConsumptionForecast;
        if (calculationType == 3) {
            surPlusPowerExtern = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }

        if (calculationType == 4) {
            surPlusPowerExtern = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }


        Double surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast;
        Double plantPowerConsumptionForecast = plant.powerConsumptionForecast;
        Double plantPowerConsumptionForecastSum = plantPowerConsumptionForecast;

        Double PVcostToSolar = plant.powerConsumptionForecast * tariff.rateConsumption;
        Double consumptionSum = consumptionSubtract1 + consumptionSubtract2 + plantPowerConsumptionForecast;
        if (calculationType == 3) {
            PVcostToSolar = (plant.powerConsumptionForecast + plant.powerConsumptionForecastMeter2) * tariff.rateConsumption;
            consumptionSum = consumptionSubtract1 + plantPowerConsumptionForecast;

            plantPowerConsumptionForecastSum += plant.powerConsumptionForecastMeter2;
            surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }

        if (calculationType == 4) {
            PVcostToSolar = (plant.powerConsumptionForecast + plant.powerConsumptionForecastMeter2) * tariff.rateConsumption;
            consumptionSum = consumptionSubtract1 + plantPowerConsumptionForecast;

            plantPowerConsumptionForecastSum += plant.powerConsumptionForecastMeter2;
            surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }

        Double creditPrediction = surPlusPower * tariff.rateExcessProductionExternal;
        Double PVsurPlusSolar = surPlusPower * tariff.rateExcessProduction;

        //subtotals
        Double subtotalExternalConsumption = 0.0;
        Double subtotalExternalPrice = 0.0;
        Double subtotalSolarConsumption = 0.0;
        Double subtotalSolarPrice = 0.0;


        if (calculationType == 3) {
            subtotalExternalConsumption = consumptionSubtract1 - surPlusPower;
            subtotalExternalPrice = consumptionSubtract1Value - creditPrediction;

            subtotalSolarConsumption = plantPowerConsumptionForecastSum + surPlusPower;
            subtotalSolarPrice = PVcostToSolar + PVsurPlusSolar;
        } else if (calculationType == 2) {

            subtotalExternalConsumption = consumptionSubtract1 + consumptionSubtract2 - surPlusPower;
            subtotalExternalPrice = consumptionSubtract1Value + consumptionSubtract2Value - creditPrediction;

            subtotalSolarConsumption = plantPowerConsumptionForecastSum + surPlusPower;
            subtotalSolarPrice = PVcostToSolar + PVsurPlusSolar;
        } else if (calculationType == 1) {
            subtotalExternalConsumption = consumptionSubtract1 - surPlusPower;
            subtotalExternalPrice = consumptionSubtract1Value - creditPrediction;

            subtotalSolarConsumption = plantPowerConsumptionForecastSum + surPlusPower;
            subtotalSolarPrice = PVcostToSolar + PVsurPlusSolar;
        } else if (calculationType == 4) {
            subtotalExternalConsumption = consumptionSubtract1 - surPlusPower;
            subtotalExternalPrice = consumptionSubtract1Value + consumptionSubtract2Value -creditPrediction;

            subtotalSolarConsumption = plantPowerConsumptionForecastSum + surPlusPower;
            subtotalSolarPrice = PVcostToSolar + PVsurPlusSolar;
        }

        //totals
        Double sumConsumption = subtotalExternalConsumption + subtotalSolarConsumption;
        Double sumConsumptionPrice = subtotalExternalPrice + subtotalSolarPrice;


        Double consumptionSumExtern = consumptionSubtract1 + consumptionSubtract2;
        Double consumptionSumValueExtern = consumptionSubtract1Value + consumptionSubtract2Value;

        //old 5.9.2022
        //Double consumptionExternTotalSumTemp = consumptionExternSumTemp - creditPrediction;

        Double consumptionExternTotalSumTemp = consumptionSumValueExtern - creditPrediction;

        Double BOIb = powerBill.consumptionValue / powerBill.consumption;
        Double planCost = tariff.planningFlatRate * plant.nominalPower;
        Double finalPrice = plant.unitPrice;

        Double finalPriceMinPrePayment = plant.unitPriceMinPrepayment;
        Double finalPriceMaxPrePayment = plant.unitPriceMaxPrepayment;
        Double prePaymentMin = plant.prePaymentMin;
        Double prePaymentMax = plant.prePaymentMax;

        Double priceMinusPrepaymentMin = finalPriceMinPrePayment - prePaymentMin;
        Double priceMinusPrepaymentMax = finalPriceMaxPrePayment - prePaymentMax;

        Double powerConsumption = powerBill.consumption - plant.powerConsumptionForecast;
        if (calculationType == 3) {
            powerConsumption = powerBill.consumption2 - plant.powerConsumptionForecastMeter2 + surPlusPower;
        }

        Double powerExpenditure = powerConsumption * BOIb;

        if (plant.joinPowerMeters == false) {
            powerExpenditure += powerBill.consumptionValue2;
        }

        if (calculationType == 3) {
            powerExpenditure = powerConsumption * (powerBill.consumptionValue / powerBill.consumption);
        }

        if (plant.prePayment == null) {
            plant.prePayment = 0.0;
        }
        Double priceMinusPrepayment = finalPrice - plant.prePayment;

        Double electricityExpenditureTotal = powerExpenditure - creditPrediction + PVcostToSolar + PVsurPlusSolar;
        Double duration = priceMinusPrepayment / (PVcostToSolar+PVsurPlusSolar);
        if (duration > 12.5) {
            duration = 12.5;
        }

        Double durationMin = priceMinusPrepaymentMin / (PVcostToSolar+PVsurPlusSolar);
        if (durationMin > 12.5) {
            durationMin = 12.5;
        }

        Double durationMax = priceMinusPrepaymentMax / (PVcostToSolar+PVsurPlusSolar);
        if (durationMax > 12.5) {
            durationMax = 12.5;
        }

        Double openBalance = priceMinusPrepayment - duration * (PVcostToSolar+PVsurPlusSolar);
        Double openBalanceMin = priceMinusPrepaymentMin - durationMin * (PVcostToSolar+PVsurPlusSolar);
        Double openBalanceMax = priceMinusPrepaymentMax - durationMax * (PVcostToSolar+PVsurPlusSolar);

        //override for now -- direktkauf

        String prepaymentAsString = GermanNumberParser.getGermanNumberFormat(plant.prePayment, 2);
        String durationAsString = GermanNumberParser.getGermanNumberFormat(duration,1);
        String openBalanceAsString = GermanNumberParser.getGermanNumberFormat(openBalance, 2);

        if (tariff.directBuy == true) {
            prepaymentAsString = GermanNumberParser.getGermanNumberFormat(finalPrice, 2);
            durationAsString = GermanNumberParser.getGermanNumberFormat(0.0,1);
            openBalanceAsString = GermanNumberParser.getGermanNumberFormat(0.0, 2);
        }

        String billPricePerKW = GermanNumberParser.getGermanNumberFormat((powerBill.consumptionValue / powerBill.consumption), 2);
        String plantNominalPower = GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2);

        String plantPowerProductionForecast = GermanNumberParser.getGermanNumberFormat(plant.powerProductionForecast,2);
        //String plantPowerConsumptionForecast = GermanNumberParser.getGermanNumberFormat(plant.powerConsumptionForecast,2);

        String tariffRateConsumption = GermanNumberParser.getGermanCurrencyFormat(tariff.rateConsumption, 3);
        String surPlusPowerAsString = GermanNumberParser.getGermanNumberFormat(surPlusPower,2);
        String tariffRateExcessProduction = GermanNumberParser.getGermanCurrencyFormat(tariff.rateExcessProduction, 3);
        String plantUnitPrice = GermanNumberParser.getGermanCurrencyFormat(plant.unitPrice, 2);
        String finalPriceAsString = GermanNumberParser.getGermanNumberFormat(finalPrice, 2);
        String powerConsumptionAsString = GermanNumberParser.getGermanNumberFormat(powerConsumption,2);
        String powerExpenditureAsString = GermanNumberParser.getGermanCurrencyFormat(powerExpenditure, 2);
        String creditPredictionAsString = GermanNumberParser.getGermanNumberFormat(creditPrediction, 2);
        String PVcostToSolarAsString = GermanNumberParser.getGermanNumberFormat(PVcostToSolar, 2);
        String PVsurPlusSolarAsString = GermanNumberParser.getGermanNumberFormat(PVsurPlusSolar, 2);
        String electricityExpenditureTotalAsString = GermanNumberParser.getGermanCurrencyFormat(electricityExpenditureTotal, 2);
        //String durationAsString = GermanNumberParser.getGermanNumberFormat(duration,1);
        //String openBalanceAsString = GermanNumberParser.getGermanNumberFormat(openBalance, 2);
        //String prepaymentAsString = GermanNumberParser.getGermanNumberFormat(plant.prePayment, 2);
        String priceMinusPrepaymentAsString = GermanNumberParser.getGermanCurrencyFormat(priceMinusPrepayment, 2);

        //new
        String billPricePerKW2 = "";
        String plantPowerConsumptionForecast2 = "";
        Boolean extraMeterExists = false;
        if (powerBill.consumptionValue2 > 0.0) {
            extraMeterExists = true;
            billPricePerKW2 = GermanNumberParser.getGermanNumberFormat((powerBill.consumptionValue2 / powerBill.consumption2), 2);
            plantPowerConsumptionForecast2 = GermanNumberParser.getGermanNumberFormat(plant.powerConsumptionForecastMeter2,2);
        }


        Double maxSubvention = 0.0;
        if (plant.nominalPower > 10) {
            //change
            //maxSubvention = 10 * tariff.subventionTo10Kw + ((plant.nominalPower - 10) * tariff.subventionFrom10Kw);
            maxSubvention = 10 * tariff.subventionTo10Kw + ((plant.nominalPower - 10) * plant.subventionFrom10Kw);
        } else {
            maxSubvention = plant.nominalPower * tariff.subventionTo10Kw;
        }

        Double maxPrePayment = Math.ceil((maxSubvention / 0.35) / 50) * 50;
        String kpc = GermanNumberParser.getGermanNumberFormat(maxSubvention, 2);

        Double subvention = plant.prePayment * 0.35;
        if (plant.prePayment >= maxPrePayment) {
            subvention = maxSubvention;
        }

        Double subventionMin = plant.prePaymentMin * 0.35;
        if (plant.prePayment >= maxPrePayment) {
            subventionMin = maxSubvention;
        }

        Double subventionMax = plant.prePaymentMax * 0.35;
        if (plant.prePayment >= maxPrePayment) {
            subventionMax = maxSubvention;
        }

        //override all
        subvention = maxSubvention;

        Boolean showPrepayment = false;
        if ((Math.abs(plant.prePayment - plant.prePaymentMin) > 1) && (Math.abs(plant.prePayment - plant.prePaymentMax) > 1)) {
            showPrepayment = true;
        }


        Boolean showOpenBalance = false;
        if (openBalance > 0) {
            showOpenBalance = true;
        }

        Double saeDiff = consumptionVal - consumptionExternTotalSumTemp;

        //image generation test
        //char eur
        QuickChart chart = new QuickChart();
        chart.setWidth(670);
        chart.setHeight(400);
        chart.setBackgroundColor("#ffffff");
        chart.setConfig("{"
                + "    type: 'bar',"
                + "    data: {"
                + "        datasets: ["
                + "         {label: 'Aktuell', backgroundColor: '#04628a',data: ["+consumptionVal+"]},"
                + "         {label: 'Bis Eigentumsübergang', backgroundColor: '#b8c2cc', data: ["+sumConsumptionPrice+"]},"
                + "         {label: 'Nach Eigentumsübergang', backgroundColor: '#7c9d2b', data: ["+consumptionExternTotalSumTemp+"]}"
                + "     ]"
                + "    },"
                + "    options: {"
                + "         legend:{position:'bottom'},"
                + "         title:{display:true, text:'Ihre Stromkosten pro Jahr', fontSize: 20,},"
                + "         plugins:{"
                + "             datalabels:{"
                + "                 anchor:'center',"
                + "                 align:'center',"
                + "                 color:'#fff',"
                + "                 font:{weight:'bold', size:16},"
                + "                 formatter:function(value,context){return Math.round(value).toString().replace(/(\\d)(?=(\\d{3})+(?!\\d))/g, \"$1.\")+' EUR';}"
                + "             }"
                +"          },"
                + "    }"
                + "}"
        );
        //+ "                 formatter:function(value,context){return Math.round(value).toString().replace(/(\\d)(?=(\\d{3})+(?!\\d))/g, \"$1.\")+' EUR';}"

        //sudo docker run -d --restart unless-stopped -p 8089:3400 ianw/quickchart
        String chartUrl = chart.getUrl().replace("https://quickchart.io/", chartServiceUrl);

        String chartFileName1 = "chart_eur_"+UUID.randomUUID().toString();
        try {
            File myFile = new File(chartFolder+chartFileName1);
            CloseableHttpClient client = HttpClients.createDefault();
            try (CloseableHttpResponse response = client.execute(new HttpGet(chartUrl))) {
                HttpEntity entity = response.getEntity();
                if (entity != null) {
                    try (FileOutputStream outStream = new FileOutputStream(myFile)) {
                        entity.writeTo(outStream);
                    }
                }
            }
        } catch (Exception e) {
            System.out.println("error generating chart");
        }

        Double consumptionDiff = consumption - plantPowerConsumptionForecastSum;
        Double consumptionDiffSum = plantPowerConsumptionForecastSum + surPlusPower;

        //chart kwp
        chart = new QuickChart();
        chart.setWidth(670);
        chart.setHeight(400);
        chart.setBackgroundColor("#ffffff");
        chart.setConfig("{"
                + "    type: 'bar',"
                + "    data: {"
                + "       labels: ['Aktuell', 'Zukunft mit solar.family'],"
                + "       datasets: ["
                + "         {label: 'Energieversorger', backgroundColor: '#04628a', data: ["+consumption+", "+consumptionDiff+"],},"
                + "         {label: 'Eigenverbrauch', backgroundColor: '#7c9d2b', data: [0, "+plantPowerConsumptionForecastSum+"],},"
                + "         {label: 'Überschuss', backgroundColor: '#c3d69b', data: [0, "+surPlusPower+"],}"
                + "       ]"
                + "    },"
                + "    options: {"
                + "         legend:{position:'right'},"
                + "         title:{display:true,text:'Stromverbrauch und Stromproduktion pro Jahr', fontSize: 20,},"
                + "         plugins:{"
                + "             datalabels:{"
                + "                 anchor:'center',"
                + "                 align:'center',"
                + "                 color:'#fff',"
                + "                 font:{weight:'bold', size:16},"
                + "                 formatter:function(value,context){if(value > 0){return Math.round(value).toString().replace(/(\\d)(?=(\\d{3})+(?!\\d))/g, \"$1.\")+' kWh';} else {return ''}}"
                + "             }"
                + "         },"
                + "         scales:{xAxes: [{stacked: true,},], yAxes: [{stacked: true,},]},"
                + "    }"
                + "}"
        );
        //+ "                 formatter:function(value,context){if(value > 0){return Math.round(value)+' kWh';} else {return ''}}"

        //sudo docker run -d --restart unless-stopped -p 8089:3400 ianw/quickchart
        chartUrl = chart.getUrl().replace("https://quickchart.io/", chartServiceUrl);

        String chartFileName2 = "chart_kwp_"+UUID.randomUUID().toString();
        try {
            File myFile = new File(chartFolder+chartFileName2);
            CloseableHttpClient client = HttpClients.createDefault();
            try (CloseableHttpResponse response = client.execute(new HttpGet(chartUrl))) {
                HttpEntity entity = response.getEntity();
                if (entity != null) {
                    try (FileOutputStream outStream = new FileOutputStream(myFile)) {
                        entity.writeTo(outStream);
                    }
                }
            }
        } catch (Exception e) {
            System.out.println("error generating chart");
        }

        String subventionAsString = GermanNumberParser.getGermanNumberFormat(subvention, 2);
        String forecastHtmlAsString = forecast_calculation
                //.data("subHeader", subHeader)
                //new
                //used
                .data("chartFileName1", chartFileName1)
                .data("chartFileName2", chartFileName2)
                .data("consumptionSumExtern", GermanNumberParser.getGermanNumberFormat(consumptionSumExtern,2))
                .data("consumptionSumValueExtern", GermanNumberParser.getGermanNumberFormat(consumptionSumValueExtern,2))
                .data("consumptionExternTemp", GermanNumberParser.getGermanNumberFormat(consumptionExternTemp,2))
                .data("consumptionExternSumTemp", GermanNumberParser.getGermanNumberFormat(consumptionExternSumTemp,2))
                .data("consumptionKwExternTemp", GermanNumberParser.getGermanNumberFormat(consumptionKwExternTemp,2))
                .data("consumptionExternTotalSumTemp", GermanNumberParser.getGermanNumberFormat(consumptionExternTotalSumTemp,2))
                .data("showPrepayment", showPrepayment)
                //.data("showDuration", showDuration)
                .data("showOpenBalance", showOpenBalance)
                .data("saeDiff", GermanNumberParser.getGermanNumberFormat(saeDiff,2))
                .data("consumptionDiffSum", GermanNumberParser.getGermanNumberFormat(consumptionDiffSum,2))

                .data("forecastTitle", plant.title)
                //.data("forecastTitle", "Prognoserechnung")
                .data("calculationType", calculationType)
                .data("nrPowerMeters", nrPowerMeters)
                .data("rateConsumption", GermanNumberParser.getGermanNumberFormat(tariff.rateConsumption, 4))
                .data("rateExcessProduction", GermanNumberParser.getGermanNumberFormat(tariff.rateExcessProduction, 4))
                .data("coefficient1", GermanNumberParser.getGermanNumberFormat(coefficient1, 4))
                .data("coefficient2", GermanNumberParser.getGermanNumberFormat(coefficient2, 4))
                .data("consumptionSubtract1", GermanNumberParser.getGermanNumberFormat(consumptionSubtract1, 2))
                .data("consumptionSubtract2", GermanNumberParser.getGermanNumberFormat(consumptionSubtract2, 2))
                .data("consumptionSubtract1Value", GermanNumberParser.getGermanNumberFormat(consumptionSubtract1Value, 2))
                .data("consumptionSubtract2Value", GermanNumberParser.getGermanNumberFormat(consumptionSubtract2Value, 2))
                .data("surPlusPower", GermanNumberParser.getGermanNumberFormat(surPlusPower,2))
                .data("rateExcessProductionExternal", GermanNumberParser.getGermanNumberFormat(tariff.rateExcessProductionExternal, 4))
                .data("creditPrediction", GermanNumberParser.getGermanNumberFormat(creditPrediction, 2))
                .data("plantPowerConsumptionForecast", GermanNumberParser.getGermanNumberFormat(plantPowerConsumptionForecast, 2))
                .data("tariffRateConsumption",GermanNumberParser.getGermanCurrencyFormat(tariff.rateConsumption, 4))
                .data("PVcostToSolar", GermanNumberParser.getGermanNumberFormat(PVcostToSolar, 2))
                .data("PVsurPlusSolar", GermanNumberParser.getGermanNumberFormat(PVsurPlusSolar, 2))
                .data("consumptionValue1", GermanNumberParser.getGermanNumberFormat(powerBill.consumptionValue, 2))
                .data("consumptionInKwh1", GermanNumberParser.getGermanNumberFormat(powerBill.consumption, 2))
                .data("consumptionValue2", GermanNumberParser.getGermanNumberFormat(powerBill.consumptionValue2, 2))
                .data("consumptionInKwh2", GermanNumberParser.getGermanNumberFormat(powerBill.consumption2, 2))
                .data("electricityExpenditureTotal", GermanNumberParser.getGermanNumberFormat(electricityExpenditureTotal, 2))
                .data("consumptionSum", GermanNumberParser.getGermanNumberFormat(consumptionSum, 2))
                .data("kpc", GermanNumberParser.getGermanNumberFormat(maxSubvention, 2))
                .data("finalPriceMinPrePayment", GermanNumberParser.getGermanNumberFormat(finalPriceMinPrePayment, 2))
                .data("finalPriceMaxPrePayment", GermanNumberParser.getGermanNumberFormat(finalPriceMaxPrePayment, 2))
                .data("prePaymentMin", GermanNumberParser.getGermanNumberFormat(prePaymentMin, 2))
                .data("prePaymentMax", GermanNumberParser.getGermanNumberFormat(prePaymentMax, 2))
                .data("priceMinusPrepaymentMin", GermanNumberParser.getGermanNumberFormat(priceMinusPrepaymentMin, 2))
                .data("priceMinusPrepaymentMax", GermanNumberParser.getGermanNumberFormat(priceMinusPrepaymentMax, 2))
                .data("priceMinusPrepaymentMax", GermanNumberParser.getGermanNumberFormat(priceMinusPrepaymentMax, 2))
                .data("subventionMin", GermanNumberParser.getGermanNumberFormat(subventionMin, 2))
                .data("subventionMax", GermanNumberParser.getGermanNumberFormat(subventionMax, 2))
                .data("durationMin", GermanNumberParser.getGermanNumberFormat(durationMin, 1))
                .data("durationMax", GermanNumberParser.getGermanNumberFormat(durationMax, 1))
                .data("openBalanceMin", GermanNumberParser.getGermanNumberFormat(openBalanceMin, 2))
                .data("openBalanceMax", GermanNumberParser.getGermanNumberFormat(openBalanceMax, 2))
                .data("surPlusPowerExtern", GermanNumberParser.getGermanNumberFormat(surPlusPowerExtern, 2))
                .data("plantPowerConsumptionForecastSum", GermanNumberParser.getGermanNumberFormat(plantPowerConsumptionForecastSum, 2))
                .data("subtotalExternalConsumption", GermanNumberParser.getGermanNumberFormat(subtotalExternalConsumption, 2))
                .data("subtotalExternalPrice", GermanNumberParser.getGermanNumberFormat(subtotalExternalPrice, 2))
                .data("subtotalSolarConsumption", GermanNumberParser.getGermanNumberFormat(subtotalSolarConsumption, 2))
                .data("subtotalSolarPrice", GermanNumberParser.getGermanNumberFormat(subtotalSolarPrice, 2))
                .data("sumConsumption", GermanNumberParser.getGermanNumberFormat(sumConsumption, 2))
                .data("sumConsumptionPrice", GermanNumberParser.getGermanNumberFormat(sumConsumptionPrice, 2))

                //do check

                .data("billPricePerKW", billPricePerKW)
                .data("plantNominalPower", plantNominalPower)
                .data("plantPowerProductionForecast", plantPowerProductionForecast)
                //.data("plantPowerConsumptionForecast", plantPowerConsumptionForecast)
                //.data("tariffRateConsumption", tariffRateConsumption)

                .data("plantUnitPrice", plantUnitPrice)
                .data("powerBill", powerBill)
                .data("plant", plant)
                .data("tariff", tariff)
                .data("surPlusPowerAsString", surPlusPowerAsString)

                .data("finalPriceAsString", finalPriceAsString)
                .data("powerConsumptionAsString", powerConsumptionAsString)
                .data("powerExpenditureAsString", powerExpenditureAsString)
                .data("creditPredictionAsString", creditPredictionAsString)
                .data("planCost", planCost)
                .data("prepaymentAsString", prepaymentAsString)
                .data("PVcostToSolarAsString", PVcostToSolarAsString)
                .data("PVsurPlusSolarAsString", PVsurPlusSolarAsString)
                .data("electricityExpenditureTotalAsString", electricityExpenditureTotalAsString)
                .data("durationAsString", durationAsString)
                .data("openBalanceAsString", openBalanceAsString)
                .data("subventionAsString", subventionAsString)
                .data("priceMinusPrepaymentAsString", priceMinusPrepaymentAsString)
                //new
                .data("consumptionInKwh", consumptionInKwh)
                .data("consumptionValue", consumptionValue)
                .data("extraMeterExists", extraMeterExists)
                /*
                .data("billPricePerKW2", billPricePerKW2)
                .data("consumptionValue1", consumptionValue1)
                .data("consumptionInKwh1", consumptionInKwh1)
                .data("consumptionValue2", consumptionValue2)
                .data("consumptionInKwh2", consumptionInKwh2)
                */

                .data("plantPowerConsumptionForecast2", plantPowerConsumptionForecast2)

                //the wild and the new
                .data("consumptionTableRowOne", GermanNumberParser.getGermanNumberFormat((powerBill.consumption - plant.powerConsumptionForecast), 2))
                .data("consumptionValueTableRowOne", GermanNumberParser.getGermanNumberFormat(((powerBill.consumption - plant.powerConsumptionForecast) * (powerBill.consumptionValue / powerBill.consumption)), 2))
                .data("consumptionTableRowTwo", GermanNumberParser.getGermanNumberFormat((powerBill.consumption2 - plant.powerConsumptionForecastMeter2), 2))
                .data("consumptionValueTableRowTwo", GermanNumberParser.getGermanNumberFormat(((powerBill.consumption2 - plant.powerConsumptionForecastMeter2) * (powerBill.consumptionValue2 / powerBill.consumption2)), 2))
                .data("consumptionTableRowFour", GermanNumberParser.getGermanNumberFormat((plant.powerConsumptionForecast + plant.powerConsumptionForecastMeter2), 2))
                .data("user", user)
                .data("extras", extras)

                .render();

        String subHeader = forecast_calculation_sub_header.data("forecastTitle", plant.title).render();
        forecastHtmlAsString = forecastHtmlAsString.replace("[[subHeader]]", subHeader);

        String htmlString =  htmlDocumentGenerator.generateHtmlDoc(forecastHtmlAsString, calculationTitle);

        String plantNominalPowerTitle = plantNominalPower.replace(",","-")+"kWp";

        try {

            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_prognoserechnung_"+plantNominalPowerTitle+"_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename +".html", htmlString) == true) {
                System.out.println("Html file created");

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();

                //CHANGE BACK TO UBUNTU!!!!
                Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + "/" + newFilename +".html", folder+"/"+ newFilename +".pdf");

                //windows server -- swotch on production!!!!
                //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename +".html", folder+"/"+ newFilename +".pdf");

                if (generate == false) {
                    File fileDownload = new File(folder + newFilename +".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + newFilename +".pdf");
                    return response.build();
                } else {
                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=21", plant.id).firstResult();

                    //save file to db
                    FileModel file = new FileModel();
                    file.fileName = newFilename +".pdf";
                    file.fileContentType = "application/pdf";
                    file.filePath = folder;
                    file.idFileContainer = fc.id;
                    file.rs = 1;
                    file.generated = true;
                    file.persist();


                    //update file container
                    fc.rs = 2;
                    fc.persist();

                    Response.ResponseBuilder response = Response.ok();
                    return response.build();
                }
            } else {
                Response.ResponseBuilder response = Response.serverError();
                return response.build();
            }
        } catch (Exception e) {
            Response.ResponseBuilder response = Response.serverError();
            return response.build();
        }
    }


    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-file-containers/{id}")
    @Transactional
    public ResultHelper getFileContainers(@PathParam("id") UUID id) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",id).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);
        Boolean showBackendContainers = true;
        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            showBackendContainers = false;
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        if (showBackendContainers == true) {
            List<FileContainerModel> fileContainerList = FileContainerModel.find("contextId=?1 and (contextType=2 or contextType=3)", Sort.by("sortOrder").ascending(), id).list();
            return new SolarFileContainerResult(200, fileContainerList);
        } else {
            List<FileContainerModel> fileContainerList = FileContainerModel.find("contextId=?1 and (contextType=2 or contextType=3) and backendOnly=?2", Sort.by("sortOrder").ascending(), id,false).list();
            return new SolarFileContainerResult(200, fileContainerList);
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("add-user-to-project/{userId}/{plantId}")
    @Transactional
    public ResultHelper addUserToProject(@PathParam("userId") UUID userId, @PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser == null) {
            ProjectUserModel insertProjectUser = new ProjectUserModel();
            insertProjectUser.userId = userId;
            insertProjectUser.plantId = plantId;
            insertProjectUser.persist();
        } else {
            projectUser.userId = userId;
            projectUser.persist();
        }

        return new ResultHelper(new ResultCommonObject(200, "ok"));
    }

    //OK 3
    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("get-project-user/{plantId}")
    @Transactional
    public ResultHelper getProjectUser(@PathParam("plantId") UUID plantId) {
        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

                ObjectMapper mapper = new ObjectMapper();
                ObjectNode node = mapper.createObjectNode();

                node.put("email", user.email);
                node.put("firstName", user.firstName);
                node.put("lastName", user.lastName);
                node.put("phone", user.phoneNr);
                node.put("id", user.id.toString());

                return new ResultHelper(200, node);
        } else {
            return new ResultHelper(new ResultCommonObject(401, "not found"));
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-tariff/{id}")
    @Transactional
    public ResultHelper getTariff(@PathParam("id") UUID id) {
        SettingsModel tariff = SettingsModel.findById(id);

        return new SolarPlantResult(200, tariff);
    }


    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-tariff-frontend/{id}")
    @Transactional
    public String getTariffFrontend(@PathParam("id") UUID id) {
        SettingsModel tariff = SettingsModel.findById(id);

        return tariff.title;
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("get-campaign/{id}")
    @Transactional
    public CampaignModel getCampaign(@PathParam("id") UUID id) {
        return CampaignModel.findById(id);
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-campaign-frontend/{id}")
    @Transactional
    public String getCampaignFrontend(@PathParam("id") UUID id) {
        CampaignModel campaign = CampaignModel.findById(id);

        return campaign.title;
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("get-user-list/{userId}")
    @Transactional
    public ResultHelper getUserList(@PathParam("userId") UUID userId) {

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
            }
        }

        String q = new StringBuilder()
                .append("select distinct p ")
                .append("from SolarPlantModel p ")
                .append("left join ProjectUserModel u on u.plantId = p.id ")
                .append("where u.userId = '"+userId+"' ")
                .append("AND (p.rs IS NULL OR p.rs < 99) ")
                .append("order by p.t0 desc ")
                .toString();

        List<SolarPlantModel> list = SolarPlantModel.find(q).list();

        return new SolarPlantResult(200, list);
    }

    //OK - 3
    //will be deprecated!
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-file-status/{id}/{status}")
    @Transactional
    public ResultHelper updateUserFileStatus(@PathParam("id") UUID id, @PathParam("status") Boolean status) {
        try {
            SolarPlantModel plant = SolarPlantModel.findById(id);
            plant.solarPlantFilesVerifiedByBackendUser = status;
            plant.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    //OK - 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-plant-status/{id}/{type}/{status}")
    @Transactional
    public ResultHelper updatePlantStatus(@PathParam("id") UUID id, @PathParam("type") String type, @PathParam("status") Boolean status) {
        try {
            SolarPlantModel plant = SolarPlantModel.findById(id);

            if (type.equals("filesVerified")) {
                plant.solarPlantFilesVerifiedByBackendUser = status;
            } else if (type.equals("contractFinalized")) {
                plant.contractFinalized = status;
            } else if (type.equals("plantInstalled")) {
                plant.plantInstalled = status;
            } else if (type.equals("plantInUse")) {
                plant.plantInUse = status;
            }

            plant.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-plant-status-with-date/{id}/{status}/{date}")
    @Transactional
    public ResultHelper updatePlantStatus(@PathParam("id") UUID id, @PathParam("status") String status, @PathParam("date") String date) {
        try {
            SolarPlantModel plant = SolarPlantModel.findById(id);

            if (status.equals("plantInUse")) {
                plant.plantInUse = true;
                plant.plantInUseDate = LocalDate.parse(date);
            }

            plant.persist();

            return new ResultHelper(new ResultCommonObject(200, "Status updated"));
        } catch (Exception e) {
            return new ResultHelper(new ResultCommonObject(400, "Status update failed!"));
        }
    }


    //OK 3
    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("property-owner-add/{plantId}")
    @Transactional
    public ResultHelper addPropertyOwnerToSolarPlant(@PathParam("plantId") UUID plantId, SolarPlantPropertyOwnerModel plant) {
        plant.plantId = plantId;
        plant.rs = 1;
        plant.persist();

        return new SolarPlantPropertyOwnerResult(200, plant);
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("property-owner-remove/{ownerId}")
    @Transactional
    public ResultCommonObject addPropertyOwnerToSolarPlant(@PathParam("ownerId") UUID ownerId) {
       SolarPlantPropertyOwnerModel.findById(ownerId).delete();

       return new ResultCommonObject(200, "OK");
    }

    //OK 3
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("property-owner-list/{plantId}")
    @Transactional
    public ResultHelper getSolarPlantPropertyOwnerList(@PathParam("plantId") UUID plantId) {
        return new SolarPlantPropertyOwnerResult(200, SolarPlantPropertyOwnerModel.find("plantId", Sort.by("person").ascending(), plantId).list());
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("power-bill/{plantId}")
    @Transactional
    public ResultHelper getPowerBill(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId", plantId).firstResult();

            return new SolarPowerBillResult(200, powerBill);
        } else {
            return new ResultHelper(new ResultCommonObject(401, "not found"));
        }

    }

    //OK 3
    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Path("power-bill/{plantId}")
    @Transactional
    public ResultHelper editPowerBill(@PathParam("plantId") UUID plantId, SolarPlantPowerBill req) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            //this should be optimized in next phase
            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                }
            }

            SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId", plantId).firstResult();
            powerBill.billNo = req.billNo;
            powerBill.billPeriod = req.billPeriod;
            powerBill.consumption = req.consumption;
            powerBill.contract = req.contract;
            powerBill.consumptionValue = req.consumptionValue;
            powerBill.netProvider = req.netProvider;
            powerBill.provider = req.provider;

            powerBill.consumption2 = req.consumption2;
            powerBill.contract2 = req.contract2;
            powerBill.consumptionValue2 = req.consumptionValue2;


            SolarPlantModel plant = SolarPlantModel.findById(plantId);
            plant.calculationInProgress = true;
            plant.powerBillSaved = true;
            plant.persist();

            return new SolarPowerBillResult(200, powerBill);
        } else {
            return new ResultHelper(new ResultCommonObject(401, "not found"));
        }

    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("inspection-update/{plantId}")
    @Transactional
    public ResultCommonObject edit(@PathParam("plantId") UUID plantId, SolarPlantModel req) {

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        if (plant != null) {

            plant.inspectionCheckDate = req.inspectionCheckDate;
            plant.inspectionEnergyComunity = req.inspectionEnergyComunity;
            plant.inspectionEnergyComunityText = req.inspectionEnergyComunityText;
            plant.inspectionWaterHeating = req.inspectionWaterHeating;
            plant.inspectionWaterHeatingText = req.inspectionWaterHeatingText;
            plant.inspectionStorage = req.inspectionStorage;
            plant.inspectionStorageText = req.inspectionStorageText;
            plant.inspectionEmergencyPower = req.inspectionEmergencyPower;
            plant.inspectionEmergencyPowerText = req.inspectionEmergencyPowerText;
            plant.inspectionChargingInfrastructure = req.inspectionChargingInfrastructure;
            plant.inspectionChargingInfrastructureText = req.inspectionChargingInfrastructureText;
            plant.inspectionComments = req.inspectionComments;
            plant.inspectionMailBackendUserSendTo = req.inspectionMailBackendUserSendTo;
            plant.inspectionCheckInProgress = true;

            try {
                plant.persist();
                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    @Path("inspection-finish/{plantId}")
    @Transactional
    public ResultCommonObject inspectionFinish(@PathParam("plantId") UUID plantId, SolarPlantModel req) {

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

        if (plant != null) {

            plant.inspectionCheckFinished = true;
            plant.inspectionCheckFinishedDate = req.inspectionCheckFinishedDate.plusHours(9);
            plant.inspectionCheckFinishedMail = req.inspectionCheckFinishedMail;

            //event mail
            DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd.MM.YYYY 'um' HH:mm");
            String formattedDateTime = plant.inspectionCheckFinishedDate.format(formatter);

            String internMailSubject = "Begehungstermin durchgeführt: "+plant.title;
            String mailText = "Begehungstermin wurde am "+formattedDateTime+" durchgeführt.";
            String eventSubject = internMailSubject;
            String eventDescription = mailText;
            Date desiredDate = java.sql.Timestamp.valueOf(plant.inspectionCheckFinishedDate);
            String sendTo = plant.inspectionCheckFinishedMail;

            //new -- why not! I warned that it is not ok!
            String[] splitTitle = plant.title.split("\\s*,\\s*");
            String plantAddress = splitTitle[1]+", "+splitTitle[0];

            try {
                plant.persist();
                calendarEventMail.sendEvent(internMailSubject,mailText,eventSubject,eventDescription,desiredDate,sendTo, plantAddress);

                ActivityModel activity = new ActivityModel();
                activity.contentType = "event-plant";
                activity.contentId = plant.id;
                activity.content = plant.title;
                activity.userId = user.id;
                activity.rs = 0;
                activity.title = "inspection_finish";

                activityService.processActivity(activity, "event-plant");

                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }



    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("update-workflow-status/{plantId}/{status}/{eventEmail}")
    @Transactional
    public ResultCommonObject updateWorkflowStatus(@PathParam("plantId") UUID plantId, @PathParam("status") String status, @PathParam("eventEmail") String eventEmail) {

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

        ActivityModel activity = new ActivityModel();
        activity.contentType = "event-plant";
        activity.contentId = plant.id;
        activity.content = plant.title;
        activity.userId = user.id;
        activity.rs = 0;

        if (plant != null) {
            LocalDateTime now = LocalDateTime.now();
            try {
                if (status.equals("inspectionMailSent")) {
                    //new -- why not! I warned that it is not ok!
                    String[] splitTitle = plant.title.split("\\s*,\\s*");
                    String plantAddress = splitTitle[1]+", "+splitTitle[0];

                    //send mail to customer
                    if (user.userRegisterMailSent != null && user.userRegisterMailSent == true) {
                        mailService.sendBackendLoginReminder(user.userId.toString(), plant.inspectionCheckDate, plantAddress);
                    } else {
                        ResultCommonObject verificationData = userService.addUserToVerificationTable(user, "password-reset");
                        mailService.sendBackendActivationMail(verificationData.getMessage(), user.userId.toString(), plant.inspectionCheckDate, plantAddress);

                        user.userRegisterMailSent = true;
                        user.persist();
                    }

                    //event mail
                    DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd.MM.YYYY 'um' HH:mm");
                    String formattedDateTime = plant.inspectionCheckDate.format(formatter);

                    String internMailSubject = "Neuer Begehungstermin: "+plant.title;
                    String mailText = "Neuer Begehungstermin ("+plant.title+") wurde für "+formattedDateTime+" vereinbart.";

                    String eventDescription = mailText;
                    String eventSubject = splitTitle[2]+" | Neuer Begehungstermin: "+plant.title;

                    Date desiredDate = java.sql.Timestamp.valueOf(plant.inspectionCheckDate);
                    String sendTo = plant.inspectionMailBackendUserSendTo;

                    calendarEventMail.sendEvent(internMailSubject,mailText,eventSubject,eventDescription,desiredDate,sendTo, plantAddress);

                    plant.inspectionMailSent = true;
                    plant.persist();

                    //activity
                    activity.title = "user_register_mail_sent";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("calculationSentToCustomer")) {
                    if (plant.calculationMailWithRegistration != null && plant.calculationMailWithRegistration == true) {
                        ResultCommonObject verificationData = userService.addUserToVerificationTable(user, "password-reset");
                        mailService.calculationSentToCustomerMailWithRegistration(user.userId.toString(), now, verificationData.getMessage());
                    } else {
                        mailService.calculationSentToCustomerMail(user.userId.toString(), now);
                    }


                    String internMailSubject = "Angebot: "+plant.title;
                    String mailText = "Angebotsstatus für Photovoltaik-Anlage: "+plant.title+" überprüfen";

                    //old
                    //String eventSubject = internMailSubject;

                    //new -- why not! I warned that it is not ok!
                    String[] splitTitle = plant.title.split("\\s*,\\s*");
                    String eventSubject = splitTitle[2]+" | Angebot: "+plant.title;

                    String eventDescription = mailText;
                    Date desiredDate = java.sql.Timestamp.valueOf(now.plusWeeks(3));
                    //send to info
                    String sendTo = notificationMail;

                    calendarEventMail.sendEvent(internMailSubject,mailText,eventSubject,eventDescription,desiredDate,sendTo, "");

                    plant.calculationSentToCustomer = true;
                    plant.calculationSentToCustomerDate = now;

                    plant.persist();

                    //activity
                    activity.title = "calculation_sent_to_customer";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("orderInterest")) {
                    plant.orderInterest = true;
                    plant.orderInterestDate = now;

                    plant.persist();
                }

                if (status.equals("contractsSentToCustomer")) {
                    plant.contractsSentToCustomer = true;
                    plant.contractsSentToCustomerDate = now;
                    plant.contractsSentToCustomerBackendUserSendTo = eventEmail;

                    mailService.contractsSentToCustomerMail(user.userId.toString(), now);

                    //new -- why not! I warned that it is not ok!
                    String[] splitTitle = plant.title.split("\\s*,\\s*");
                    String eventSubject = splitTitle[2]+" | Mailversand - Vertragsunterlagen: "+plant.title;
                    String eventDescription = "Die Vertragsunterlagen für solar.family Photovoltaik-Anlage steht zum Download zur Verfügung";

                    Date desiredDate = java.sql.Timestamp.valueOf(now.plusWeeks(3));
                    String sendTo = plant.inspectionMailBackendUserSendTo;

                    calendarEventMail.sendEvent(eventSubject,eventDescription,eventSubject,eventDescription,desiredDate,sendTo, "");
                    plant.persist();

                    activity.title = "contracts_sent_to_customer";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("contractFilesChecked")) {
                    plant.contractFilesChecked = true;
                    plant.contractFilesCheckedDate = now;

                    mailService.contractsCheckedToCustomerMail(user.userId.toString(), now);

                    plant.persist();

                    activity.title = "contract_files_checked";
                    activityService.processActivity(activity, "event-plant");
                    
                }

                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
                //return new ResultCommonObject(400, e.toString());
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("simple-status-update/{plantId}/{status}/{value}")
    @Transactional
    public ResultCommonObject updateWorkflowStatus(@PathParam("plantId") UUID plantId, @PathParam("status") String status, @PathParam("value") Boolean value) {
        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        if (plant != null) {
            LocalDateTime now = LocalDateTime.now();

            ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            try {
                if (status.equals("powerBillUploaded")) {
                    plant.powerBillUploaded = value;
                    plant.persist();
                }

                if (status.equals("planDocumentUploaded")) {
                    plant.planDocumentUploaded = value;
                    plant.persist();
                }

                if (status.equals("calculationFinished")) {
                    plant.calculationFinished = value;
                    plant.persist();
                }

                if (status.equals("calculationChecked")) {
                    plant.calculationChecked = value;
                    plant.persist();
                }

                if (status.equals("contractsReviewed")) {
                    plant.contractsReviewed = value;
                    plant.persist();
                }

                if (status.equals("contractsSigned")) {
                    plant.contractsSigned = value;
                    plant.persist();
                }

                if (status.equals("inspectionChecked")) {
                    plant.inspectionChecked = value;
                    plant.persist();
                }

                if (status.equals("propertyOwnerListFinished")) {
                    plant.propertyOwnerListFinished = value;
                    plant.persist();
                }

                if (status.equals("calculationMailWithRegistration")) {
                    plant.calculationMailWithRegistration = value;
                    plant.persist();
                }

                if (status.equals("cancelByCustomer")) {
                    plant.cancelByCustomer = true;
                    plant.persist();

                    ActivityModel activity = new ActivityModel();
                    activity.contentType = "event-plant";
                    activity.contentId = plant.id;
                    activity.content = plant.title;
                    activity.userId = user.id;
                    activity.rs = 0;
                    activity.title = "cancel_by_customer";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("contractFilesChecked")) {
                    plant.contractFilesChecked = true;
                    plant.contractFilesCheckedDate = now;
                    plant.persist();

                    ActivityModel activity = new ActivityModel();
                    activity.contentType = "event-plant";
                    activity.contentId = plant.id;
                    activity.content = plant.title;
                    activity.userId = user.id;
                    activity.rs = 0;
                    activity.title = "contract_files_checked";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("orderInterestAccepted")) {
                    plant.orderInterestAccepted = true;
                    plant.orderInterestAcceptedDate = now;
                    plant.persist();

                    ActivityModel activity = new ActivityModel();
                    activity.contentType = "event-plant";
                    activity.contentId = plant.id;
                    activity.content = plant.title;
                    activity.userId = user.id;
                    activity.rs = 0;
                    activity.title = "order_interest_accepted";
                    activityService.processActivity(activity, "event-plant");
                }

                if (status.equals("contractsSentToCustomer")) {
                    plant.contractsSentToCustomer = true;
                    plant.contractsSentToCustomerDate = now;
                    plant.persist();

                    ActivityModel activity = new ActivityModel();
                    activity.contentType = "event-plant";
                    activity.contentId = plant.id;
                    activity.content = plant.title;
                    activity.userId = user.id;
                    activity.rs = 0;
                    activity.title = "contracts_sent_to_customer";
                    activityService.processActivity(activity, "event-plant");
                }

                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("simple-status-update-frontend/{plantId}/{status}/{value}")
    @Transactional
    public ResultCommonObject updateWorkflowStatusFrontend(@PathParam("plantId") UUID plantId, @PathParam("status") String status, @PathParam("value") Boolean value) {

        if (securityIdentity.getRoles().contains("user")) {

            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultCommonObject(403, "FORBIDDEN");
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

        ActivityModel activity = new ActivityModel();
        activity.contentType = "event-plant";
        activity.contentId = plant.id;
        activity.content = plant.title;
        activity.userId = user.id;
        activity.rs = 0;

        if (plant != null) {
            LocalDateTime now = LocalDateTime.now();
            try {
                if (status.equals("orderInterestAccepted")) {
                    plant.orderInterestAccepted = true;
                    plant.orderInterestAcceptedDate = now;

                    /*
                    //sf user should update this status -- adding and mixing statuses on the fly
                    plant.orderInterest = true;
                    plant.orderInterestDate = now;
                     */

                    plant.persist();

                    //activity
                    activity.title = "order_interest_accepted";
                    activityService.processActivity(activity, "event-plant");

                    mailService.contractAcceptedBakofficeMail(plant.title, now);
                }

                if (status.equals("roofImagesUploadedByClient")) {
                    plant.roofImagesUploadedByClient = true;
                    plant.roofImagesUploadedByClientDate = now;
                    plant.persist();

                    mailService.roofImagesUploadedByClientMail(plant.title, now, plant.inspectionMailBackendUserSendTo);
                }

                if (status.equals("contractSignedAndUploaded")) {
                    plant.contractSignedAndUploaded = true;
                    plant.contractSignedAndUploadedDate = now;
                    plant.persist();

                    mailService.contractSignedAndUploadedtMail(plant.title, now, plant.inspectionMailBackendUserSendTo);


                    String internMailSubject = "Vertragsunterlagen "+plant.title+" unterzeichnet";
                    String mailText = "Kunde hat Vertragsunterlagen fur "+plant.title+" unterzeichnet";

                    //old
                    //String eventSubject = internMailSubject;

                    //new -- why not! I warned that it is not ok!
                    String[] splitTitle = plant.title.split("\\s*,\\s*");
                    String eventSubject = splitTitle[2]+" | Vertragsunterlagen unterzeichnet: "+plant.title;

                    String eventDescription = mailText;
                    Date desiredDate = java.sql.Timestamp.valueOf(now.plusWeeks(1));
                    //send to info
                    String sendTo = plant.inspectionMailBackendUserSendTo;

                    calendarEventMail.sendEvent(internMailSubject,mailText,eventSubject,eventDescription,desiredDate,sendTo, "");
                }

                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }

    //OK 3
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("contract-download-status/{plantId}")
    @Transactional
    public ResultCommonObject checkContractDownloadStatus(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            }

            List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN (24,25,27,29,291,201)", plantId).list();

            for (FileContainerModel fc : fcList){
                FileModel fm = FileModel.find("idFileContainer=?1 AND (generated=true OR backendUserUpload=true)", Sort.by("t0").descending(),fc.id).firstResult();
                if (!Boolean.TRUE.equals(fm.downloadedByUser)) {
                    return new ResultCommonObject(299, "Unread Documents!");
                }
            }

            return new ResultCommonObject(200, "OK");
            //return new ResultCommonObject(299, "Unread Documents!");
        } else {
            return new ResultCommonObject(401, "not found");
        }
    }

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("contract-upload-status/{plantId}")
    @Transactional
    public ResultCommonObject checkContractUploadStatus(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            }

            List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN (24,25,27,29,291,201)", plantId).list();

            for (FileContainerModel fc : fcList){
                Long nr =  FileModel.count("idFileContainer=?1 AND ((generated is null OR generated = false) AND (backendUserUpload is null OR backendUserUpload = false))", fc.id);

                if (nr == 0) {
                    return new ResultCommonObject(299, "Missing Documents!");
                }
            }

            return new ResultCommonObject(200, "OK");
            //return new ResultCommonObject(299, "Unread Documents!");
        } else {
            return new ResultCommonObject(401, "not found");
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("segment-upload-check/{plantId}/{type}")
    @Transactional
    public ResultCommonObject segmentUploadCheck(@PathParam("plantId") UUID plantId, @PathParam("type") String type) {

        String fileType = "";
        if (type.equals("calculation_letter")) {
            fileType = "21,23";
        } if (type.equals("contract")) {
            fileType = "24,25,27,29,291,201";
        }

        List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN ("+fileType+")", plantId).list();

        for (FileContainerModel fc : fcList){
            Long nr =  FileModel.count("idFileContainer=?1 AND (generated=true OR backendUserUpload=true)", fc.id);

            if (nr == 0) {
                return new ResultCommonObject(299, "Missing Documents!");
            }
        }

        return new ResultCommonObject(200, "OK");
    }

    /*
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("contract-download-status/{plantId}")
    @Transactional
    public ResultCommonObject checkContractDownloadStatus(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            }

            List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN (24,25,27,29,291)", plantId).list();

            for (FileContainerModel fc : fcList){
                if (FileModel.count("idFileContainer=?1 AND (generated=true OR backendUserUpload=true)", fc.id) !=
                        FileModel.count("idFileContainer=?1 AND (generated=true OR backendUserUpload=true) AND downloadedByUser=true", fc.id) ) {
                    return new ResultCommonObject(299, "Unread Documents!");
                }
            }

            return new ResultCommonObject(200, "OK");
        } else {
            return new ResultCommonObject(401, "not found");
        }
    }
    */

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("offer-download-status/{plantId}")
    @Transactional
    public ResultCommonObject checkOfferDownloadStatus(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            }

            List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN (21,22,23)", plantId).list();

            for (FileContainerModel fc : fcList){
                FileModel fm = FileModel.find("idFileContainer=?1 AND (generated=true OR backendUserUpload=true)", Sort.by("t0").descending(),fc.id).firstResult();
                if (!Boolean.TRUE.equals(fm.downloadedByUser)) {
                    return new ResultCommonObject(299, "Unread Documents!");
                }
            }

            return new ResultCommonObject(200, "OK");
        } else {
            return new ResultCommonObject(401, "not found");
        }
    }

    /* OLD
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("offer-download-status/{plantId}")
    @Transactional
    public ResultCommonObject checkOfferDownloadStatus(@PathParam("plantId") UUID plantId) {

        ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();

        if (projectUser != null) {
            UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

            if(securityIdentity.getRoles().contains("user")) {
                if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                    return new ResultCommonObject(403, "Forbidden");
                }
            }

            List<FileContainerModel> fcList = FileContainerModel.find("contextId=?1 AND type IN (21,22,23)", plantId).list();

            for (FileContainerModel fc : fcList){
                if (FileModel.count("idFileContainer=?1 AND (generated=true OR backendUserUpload=true)", fc.id) !=
                        FileModel.count("idFileContainer=?1 AND (generated=true OR backendUserUpload=true) AND downloadedByUser=true", fc.id) ) {
                    return new ResultCommonObject(299, "Unread Documents!");
                }
            }

            return new ResultCommonObject(200, "OK");
        } else {
            return new ResultCommonObject(401, "not found");
        }
    }
    */

    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("get-progress-status/{plantId}")
    @Transactional
    public StatusModel getProgressStatus(@PathParam("plantId") UUID plantId) {

        if (securityIdentity.getRoles().contains("user")) {

            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                //return new ResultHelper(new ResultCommonObject(403, "Forbidden"));
                StatusModel res = new StatusModel();
                res.powerBillNr = Long.valueOf(-1);
                return res;
            }
        }

        StatusModel res = new StatusModel();
        res.powerBillNr = statusService.countPowerBillUploads(plantId);
        res.planPhotosNr = statusService.countPlanPhotos(plantId);
        res.planDocumentNr = statusService.countPlanDocumentUploads(plantId);

        return res;
    }

    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("get-contract-status/{plantId}")
    @Transactional
    public ResultCommonObject getContractStatus(@PathParam("plantId") UUID plantId) {

        if (securityIdentity.getRoles().contains("user")) {

            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultCommonObject(403, "FORBIDDEN");
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        return new ResultCommonObject(200, plant.contractSignedAndUploaded.toString());
    }

    @POST
    @RolesAllowed({"manager", "admin"})
    /*
    @Produces(MediaType.APPLICATION_JSON)
    @Consumes(MediaType.APPLICATION_JSON)
    */
    @Path("update-container-comment/{id}")
    @Transactional
    public ResultCommonObject updateContainerComment(@PathParam("id") UUID id, String comment) {

        System.out.println(comment);

        FileContainerModel cm = FileContainerModel.findById(id);
        if (cm != null) {
            cm.comment = comment;

            try {
                cm.persist();
                return new ResultCommonObject(200, "OK");
            } catch (Exception e) {
                return new ResultCommonObject(400, "BAD REQUEST");
            }
        } else {
            return new ResultCommonObject(401, "NOT FOUND");
        }
    }

    @GET
    @RolesAllowed({"user","manager", "admin"})
    @Path("last-view/{id}/{preventAdminUpdate}")
    @Transactional
    public Boolean updateLastView(@PathParam("id") UUID id, @PathParam("preventAdminUpdate") Boolean preventAdminUpdate) {
        if((securityIdentity.getRoles().contains("admin")) && (preventAdminUpdate == true)) {
            return false;
        }

        if(securityIdentity.getRoles().contains("user")) {

            ProjectUserModel pu = ProjectUserModel.find("plantId", id).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return false;
            }
        }



        SolarPlantModel plant = SolarPlantModel.findById(id);

        plant.lastOpenedById = UUID.fromString(token.getSubject());
        plant.lastOpenedByName = token.claim("given_name").get().toString() + " " + token.claim("family_name").get();
        plant.lastOpenedByUserName = token.getName();
        plant.lastOpenedDate = LocalDateTime.now();
        plant.persist();

        return true;
    }

    //one timer
    @GET
    //@RolesAllowed({"user","manager", "admin"})
    @Path("update-empty-last-view")
    @Transactional
    public Boolean updateEmptyLastView(@PathParam("id") UUID id) {
        List<SolarPlantModel> plantList = SolarPlantModel.find("lastOpenedById is null").list();

        for (SolarPlantModel plant : plantList) {
            System.out.println("Plant id: "+plant.id);

            plant.lastOpenedById = plant.createdById;
            plant.lastOpenedByName = plant.createdByName;
            plant.lastOpenedDate = plant.t0;

            plant.persist();
        }

        return true;
    }

    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Path("plant-preview-info/{plantId}")
    public PlantPreviewModel getPlantPreviewInfo(@PathParam("plantId") UUID plantId) {
        if (securityIdentity.getRoles().contains("user")) {
            PlantPreviewModel res = new PlantPreviewModel();

            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
            UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return res;
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        SettingsModel tariff = SettingsModel.findById(plant.tariff);
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId",plantId).firstResult();

        Integer calculationType = 1;
        if (powerBill.consumption2 > 0.0) {
            calculationType = 2;
        }
        if (plant.joinPowerMeters == true) {
            calculationType = 3;
        }
        if (plant.communityPlant == true) {
            calculationType = 4;
        }

        //

        Double surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast;
        if (calculationType == 3) {
            surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }

        if (calculationType == 4) {
            surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast - plant.powerConsumptionForecastMeter2;
        }
        Double PVsurPlusSolar = surPlusPower * tariff.rateExcessProduction;

        Double subvention = plant.prePaymentMax * 0.35;
        Double prePayment = plant.prePayment;

        Double finalPrice = plant.unitPrice;
        Double priceMinusPrepayment = finalPrice - plant.prePayment;

        Double PVcostToSolar = plant.powerConsumptionForecast * tariff.rateConsumption;
        if (calculationType == 3) {
            PVcostToSolar = (plant.powerConsumptionForecast + plant.powerConsumptionForecastMeter2) * tariff.rateConsumption;
        }

        if (calculationType == 4) {
            PVcostToSolar = (plant.powerConsumptionForecast + plant.powerConsumptionForecastMeter2) * tariff.rateConsumption;
        }

        Double duration = priceMinusPrepayment / (PVcostToSolar+PVsurPlusSolar);
        if (duration > 12.5) {
            duration = 12.5;
        }

        Double openBalance = priceMinusPrepayment - duration * (PVcostToSolar+PVsurPlusSolar);
        Boolean directBuy = false;

        if (tariff.directBuy == true) {
            prePayment = plant.unitPrice;
            duration = 0.0;
            openBalance = 0.0;
            directBuy = true;
        }

        PlantPreviewModel res = new PlantPreviewModel();
        res.unitPrice = plant.unitPrice;
        res.subvention = subvention;
        res.prePayment = Math.round(prePayment);
        res.duration = duration;
        res.openBalance = openBalance;
        res.directBuy = directBuy;
        res.prePaymentMax = Math.round((plant.prePaymentMax/50))*50;
        res.prePaymentMin = Math.round((plant.prePaymentMin/50))*50;
        
        return res;
    }

    @POST
    @RolesAllowed({"user", "manager", "admin"})
    @Path("frontend-calculation-message/{plantId}")
    @Transactional
    public ResultCommonObject frontendCalculationMessage(@PathParam("plantId") UUID plantId, FrontendMsgToBackoffice req) {
        ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel u = UserBasicInfoModel.find("id", pu.userId).firstResult();

        if (securityIdentity.getRoles().contains("user")) {
            if (u.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return new ResultCommonObject(403, "Frobidden");
            }
        }

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        //ProjectUserModel projectUser = ProjectUserModel.find("plantId",plantId).firstResult();
        //UserBasicInfoModel user = UserBasicInfoModel.findById(projectUser.userId);

        //create activity
        ActivityModel activity = new ActivityModel();
        activity.contentType = "frontend-msg";
        activity.contentId = plantId;
        activity.content = req.msg;
        activity.userId = u.id;
        activity.rs = 0;

        if (req.type == 1) {
            activity.title = "new_prepayment";
            activity.content = req.value.toString();
        } else if (req.type == 2) {
            activity.title = "direct_buy";
            activity.content = req.msg;
        } else if (req.type == 3) {
            activity.title = "request_project_change";
            activity.content = req.msg;
        } else if (req.type == 4) {
            activity.title = "request_contract_change";
            activity.content = req.msg;
        }

        activityService.processActivity(activity, "frontend-msg");
        mailService.frontendUserMessage(plant, req);

        return new ResultCommonObject(200, "OK");
    }

    class SolarPlantResult extends ResultHelper {
        public long records = -1 ;

        /*
        public SolarPlantResult(Integer status, Long records, List<SolarPlantModel> plantList) {
            super();
            this.records = records;
            this.status = status;
            this.payload = plantList;
        }
        */

        public SolarPlantResult(Integer status, Long records, List<ObjectNode> plantList) {
            super();
            this.records = records;
            this.status = status;
            this.payload = plantList;
        }

        public SolarPlantResult(Integer status, SolarPlantModel plant) {
            super();
            this.status = status;
            this.payload = plant;
        }

        public SolarPlantResult(Integer status, SettingsModel tariff) {
            super();
            this.status = status;
            this.payload = tariff;
        }

        public SolarPlantResult(Integer status, List<SolarPlantModel> plantList) {
            super();
            this.status = status;
            this.payload = plantList;
        }

        public Long getRecords() {
            return records;
        }
    }

    class SolarPlantPropertyOwnerResult extends ResultHelper {

        public SolarPlantPropertyOwnerResult(Integer status, SolarPlantPropertyOwnerModel plant) {
            super();
            this.status = status;
            this.payload = plant;
        }

        public SolarPlantPropertyOwnerResult(Integer status, List<SolarPlantPropertyOwnerModel> plantList) {
            super();
            this.status = status;
            this.payload = plantList;
        }
    }

    //move this to separate file in optimizing phase // no time
    class SolarFileResult extends ResultHelper {

        public SolarFileResult(Integer status, List<FileModel> files) {
            super();
            this.status = status;
            this.payload = files;
        }
    }

    class SolarFileContainerResult extends ResultHelper {
        public SolarFileContainerResult(Integer status, List<FileContainerModel> files) {
            super();
            this.status = status;
            this.payload = files;
        }
    }

    class SolarPowerBillResult extends ResultHelper {

        public SolarPowerBillResult(Integer status, SolarPlantPowerBill bill) {
            super();
            this.status = status;
            this.payload = bill;
        }
    }
}