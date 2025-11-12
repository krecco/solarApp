package org.bgr.resource;

import io.quarkus.panache.common.Sort;
import io.quarkus.qute.Template;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.helper.*;
import org.bgr.model.*;
import org.bgr.model.api.DropdownModel;
import org.bgr.model.api.ExtrasTableModel;
import org.bgr.model.db.*;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.eclipse.microprofile.jwt.JsonWebToken;

import javax.annotation.security.PermitAll;
import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.mail.internet.ContentDisposition;
import javax.transaction.Transactional;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.Produces;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.io.File;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@ApplicationScoped
@Path("file-generator")
public class ProjectFileGeneratingResource {
    @ConfigProperty(name = "app.folder.documents")
    String documentFolder;

    @Inject
    SecurityIdentity securityIdentity;

    @Inject
    JsonWebToken token;

    @Inject
    HtmlDocumentGenerator htmlDocumentGenerator;

    @Inject
    FileHelper fileHelper;

    @Inject
    Template project_participation;

    @Inject
    Template contract_energy_saving;

    @Inject
    Template contract_billing_sheet;

    @Inject
    Template mandate_completion;

    @Inject
    Template mandate_billing;

    @Inject
    Template mandate_billing_net;

    @Inject
    Template sepa;

    @Inject
    Template project_plan_v1;

    public String getFormattedDateAsString() {
        return LocalDate.now().format(DateTimeFormatter.ofPattern("dd.MM. yyyy"));
    }


    public String generateTitleString(UserBasicInfoModel user) {
        StringBuilder title = new StringBuilder();

        if (user.gender == 1) {
            title.append("Sehr geehrter Herr ");
        } if (user.gender == 2) {
            title.append("Sehr geehrte Frau ");
        } if (user.gender == 3) {
            title.append("Sehr geehrte(r) ");
        }

        if (user.gender == 1 || user.gender == 2 || user.gender == 3) {
            if (user.titlePrefix != null && user.titlePrefix.length() > 0) {
                title.append(user.titlePrefix);
                title.append(" ");
            }
            title.append(user.firstName);
            title.append(" ");
            title.append(user.lastName);
            if (user.titleSuffix != null && user.titleSuffix.length() > 0) {
                title.append(", ");
                title.append(user.titleSuffix);
            }
        }

        if (user.gender == 11) {
            title.append("Sehr geehrte Famile ");
            title.append(user.lastName);
        }

        if (user.gender == 12) {
            title.append("Sehr geehrte Damen und Herren vom Vereinsvorstand ");
        }

        if (user.gender == 13) {
            title.append("Sehr geehrte Damen und Herren vom Gemeindevorstand ");
        }

        title.append(",");
        return title.toString();
    }

    //OPEN
    @GET
    @RolesAllowed({"user", "manager", "admin"})
    @Transactional
    @Path("project-participation/{plantId}/{userId}/{generate}")
    public Response generateProjectParticipation(@PathParam("plantId") UUID plantId, @PathParam("userId") UUID userId, @PathParam("generate") Boolean generate) {
    //A_A-solar.family Anschreiben V1.0_20210403

        //get user and user address data
        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);
        String userTitle = generateTitleString(user);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        SettingsModel tariff = SettingsModel.findById(plant.tariff);

        //min max prepayment
        Double minPrepayment = tariff.kWpPrice * plant.nominalPower * 0.10;

        Double maxSubvention = 0.0;
        if (plant.nominalPower > 10) {
            maxSubvention = 10 * tariff.subventionTo10Kw + ((plant.nominalPower - 10) * tariff.subventionFrom10Kw);
        } else {
            maxSubvention = plant.nominalPower * tariff.subventionTo10Kw;
        }

        Double maxPrePayment = maxSubvention / 0.35;

        String minPrepaymentAsString = GermanNumberParser.getGermanNumberFormat(minPrepayment, 2);
        String maxPrepaymentAsString = GermanNumberParser.getGermanNumberFormat(maxPrePayment, 2);
        String validUntilDate = LocalDate.now().plusWeeks(3).format(DateTimeFormatter.ofPattern("dd.MM. yyyy"));
        String plantNominalPower = GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2);
        String tariffSpikePrice = GermanNumberParser.getGermanNumberFormat(tariff.spikePrice,2);
        String tariffSurgePrice = GermanNumberParser.getGermanNumberFormat(tariff.surgePrice,2);
        String tariffPricePowerLanAdapter = GermanNumberParser.getGermanNumberFormat(tariff.pricePowerLanAdapter,2);
        String tariffSubventionTo10Kw = GermanNumberParser.getGermanNumberFormat(tariff.subventionTo10Kw,2);
        String tariffSubventionFrom10Kw = GermanNumberParser.getGermanNumberFormat(tariff.subventionFrom10Kw,2);

        //List<ExtrasModel> extrasList = ExtrasModel.find("rs=?1 and active=?2", Sort.by("active", Sort.Direction.Descending).and("title", Sort.Direction.Ascending),1,true).list();
        List<SolarPlantExtrasModel> extrasList = SolarPlantExtrasModel.find("plantId=?1", Sort.by("title", Sort.Direction.Ascending),plantId).list();

        Double extrasSum = 0.0;
        Boolean extrasSelected = false;
        List<ExtrasTableModel> extras = new ArrayList<>();
        for (SolarPlantExtrasModel et : extrasList) {
            ExtrasTableModel obj = new ExtrasTableModel();
            obj.id = et.id;
            obj.included = "nein";
            if (et.active == true) {
                obj.included = "ja";
                extrasSum += (et.price);
                extrasSelected = true;
            }
            obj.title = et.title;
            obj.price = GermanNumberParser.getGermanNumberFormat(et.price*1.2,2);
            extras.add(obj);
        }

        if (plant.additionalCost == null) {
            plant.additionalCost = 0.0;
        }
        Double prePaymentPrice = ((tariff.kWpPrice * ((100+tariff.kWpPriceSurchargeDirectBuy)/100) * plant.nominalPower) + plant.planFee + plant.additionalCost) * 1.2;
        if (plant.buildingPermitCosts != null && plant.buildingPermitCosts == true) {
            prePaymentPrice += tariff.buildingPermitCosts;
        }

        Double prePaymentPriceWithExtras = prePaymentPrice + (extrasSum*1.2);

        String folder = documentFolder+"generated/documents/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(
            project_participation
                    .data("user", user)
                    .data("userTitle", userTitle)
                    .data("address", address)
                    .data("plant", plant)
                    .data("minPrepaymentAsString", minPrepaymentAsString)
                    .data("maxPrepaymentAsString", maxPrepaymentAsString)
                    .data("plantNominalPower", plantNominalPower)
                    .data("tariffSpikePrice", tariffSpikePrice)
                    .data("tariffSurgePrice", tariffSurgePrice)
                    .data("tariffPricePowerLanAdapter", tariffPricePowerLanAdapter)
                    .data("tariffSubventionTo10Kw", tariffSubventionTo10Kw)
                    .data("tariffSubventionFrom10Kw", tariffSubventionFrom10Kw)
                    .data("tariff", tariff)
                    .data("date", getFormattedDateAsString())
                    .data("validUntilDate", validUntilDate)
                    .data("extras", extras)
                    .data("prePaymentPrice", GermanNumberParser.getGermanCurrencyFormat(prePaymentPrice, 2))
                    .data("buildingPermitCosts", GermanNumberParser.getGermanNumberFormat(tariff.buildingPermitCosts, 2))
                    .data("additionalCost", GermanNumberParser.getGermanNumberFormat(plant.additionalCost*1.2, 2))
                    .data("extrasSumWtax", GermanNumberParser.getGermanNumberFormat(extrasSum*1.2, 2))
                    .data("prePaymentPriceWithExtras", GermanNumberParser.getGermanNumberFormat(prePaymentPriceWithExtras, 2))
                    .data("extrasSelected", extrasSelected)
                    .render()
            ,user.lastName+", "+address.street
        );
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_anschreiben_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                System.out.println("project_participation | Html file created");

                if (generate == false) {
                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();

                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("project_participation | PDF create Status: "+status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + newFilename+".pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();

                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("project_participation | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=23", plant.id).firstResult();

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
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();
            return response.build();}

        /*
        * Bearbeiter: [Bearbeiter BO]
        * SONNENKRAFTWERK OLLERSDORF IV [BOT1]
        * Projektierungsunterlagen und Prognoserechnung 3,78 kWp [BOZ1] PV-Anlage
        * */
    }

    @GET
    @Transactional
    @RolesAllowed({"user", "manager", "admin"})
    @Path("contract-energy-saving/{plantId}/{userId}/{generate}")
    public Response generateEnergySavingContract(@PathParam("plantId") UUID plantId, @PathParam("userId") UUID userId, @PathParam("generate") Boolean generate) {
        //A_D-solar.family Vertrag Energieeinsparung V1.0_20210403

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        List<SolarPlantPropertyOwnerModel> otherPropertyOwners = SolarPlantPropertyOwnerModel.find("plantId", plantId).list();
        Integer otherPropertyOwnerNr = otherPropertyOwners.size();

        String folder = documentFolder+"generated/contract/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(contract_energy_saving.data("user", user).data("address", address).data("otherPropertyOwnerNr", otherPropertyOwnerNr).data("otherPropertyOwners", otherPropertyOwners).render());
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_vertrag_energieeinsparung_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                System.out.println("project_participation | Html file created");

                //don't use injection as it will be an external function
                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();

                if (generate == false) {
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", folder + newFilename + ".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("project_participation | PDF create Status: " + status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + "contract_energy_saving.pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("vertrag_energieeinsparung | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=24", plantId).firstResult();

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
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();
            return response.build();
        }

    }

    @GET
    @Transactional
    @RolesAllowed({"user", "manager", "admin"})
    @Path("contract-billing-sheet/{plantId}/{userId}/{generate}")
    public Response generateContractBillingSheet(@PathParam("plantId") UUID plantId, @PathParam("userId") UUID userId, @PathParam("generate") Boolean generate) {
        //A_E-solar.family Vertrag Verrechnungsblatt V1.0_20210403

        UserBasicInfoModel user = UserBasicInfoModel.findById(userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        SettingsModel tariff = SettingsModel.findById(plant.tariff);
        List<SolarPlantPropertyOwnerModel> otherPropertyOwners = SolarPlantPropertyOwnerModel.find("plantId", plantId).list();
        Integer otherPropertyOwnerNr = otherPropertyOwners.size();

        //calculations
        Double planCost = tariff.planningFlatRate * plant.nominalPower;

        Double sumValue = plant.unitPrice;
        //Double sumValue = 0.0;

        String productionPerKw =  GermanNumberParser.getGermanNumberFormat(tariff.rateExcessProduction*100, 2);
        String consumptionPerKw = GermanNumberParser.getGermanNumberFormat(tariff.rateConsumption * 100, 2);

        String plantNominalPower = GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2);
        String sumValueAsString = GermanNumberParser.getGermanNumberFormat(sumValue,2);
        String serviceFeeAsString = GermanNumberParser.getGermanNumberFormat(plant.serviceFee,2);

        String folder = documentFolder+"generated/contract/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(
            contract_billing_sheet
                    .data("user", user)
                    .data("address", address)
                    .data("plant", plant)
                    .data("plantNominalPower", plantNominalPower)
                    .data("sumValueAsString", sumValueAsString)
                    .data("serviceFeeAsString", serviceFeeAsString)
                    .data("productionPerKw", productionPerKw)
                    .data("consumptionPerKw", consumptionPerKw)
                    .data("otherPropertyOwners", otherPropertyOwners)
                    .data("otherPropertyOwnerNr", otherPropertyOwnerNr)
                    .render()
        );
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_vertrag_verrechnungsblatt_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                System.out.println("vertrag_verrechnungsblatt_ | Html file created");

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                if (generate == false) {
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", folder + newFilename + ".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("project_participation | PDF create Status: " + status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + "contract_energy_saving.pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("vertrag_verrechnungsblatt_ | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=25", plantId).firstResult();

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
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();
            return response.build();
        }
    }

    @GET
    @Transactional
    @RolesAllowed({"user", "manager", "admin"})
    @Produces(MediaType.APPLICATION_OCTET_STREAM)
    @Path("mandate-completion/{plantId}/{generate}")
    public Response generateMandateCompletion(@PathParam("plantId") UUID plantId, @PathParam("generate") Boolean generate) {
        //A_F-solar.family Vollmacht Abwicklung V1.0_20210403

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        ProjectUserModel project = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);
        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

        //new
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId", plantId).firstResult();

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        String folder = documentFolder+"generated/documents/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(mandate_completion.data("plant", plant).data("user", user).data("address", address).data("powerBill", powerBill).render(), "", false);
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_vollmacht_abwicklung_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                System.out.println("vollmacht_abwicklung_ | Html file created");

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                if (generate == false) {
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", folder + newFilename + ".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("vollmacht_abwicklung_ | PDF create Status: " + status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + "contract_energy_saving.pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("vollmacht_abwicklung_ | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=27", plantId).firstResult();

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

    @GET
    @Transactional
    @RolesAllowed({"user", "manager", "admin"})
    @Path("mandate-billing/{plantId}/{generate}")
    public Response generateMandateBilling(@PathParam("plantId") UUID plantId, @PathParam("generate") Boolean generate) {
        //this is only temp generator, clean return code!
        //A_G-solar.family Vollmacht Energieabrechnung V1.0_20210403

        ProjectUserModel project = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

        //UserPowerBill powerBill = UserPowerBill.find("userId", user.userId).firstResult();
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId",plantId).firstResult();

        String folder = documentFolder+"generated/documents/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(mandate_billing.data("user", user).data("address", address).data("powerBill", powerBill).render(), "", false);
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_vollmacht_energieabrechnung_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                if (generate == false) {
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", folder + newFilename + ".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("vollmacht_energieabrechnung_ | PDF create Status: " + status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + "contract_energy_saving.pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("vollmacht_energieabrechnung_ | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=29", plantId).firstResult();

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
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();

            return response.build();
        }
    }

    @GET
    @Transactional
    @RolesAllowed({"user", "manager", "admin"})
    @Path("mandate-billing-net/{plantId}/{generate}")
    public Response generateMandateBillingNet(@PathParam("plantId") UUID plantId, @PathParam("generate") Boolean generate) {
        //this is only temp generator, clean return code!
        //A_G-solar.family Vollmacht Energieabrechnung V1.0_20210403

        ProjectUserModel project = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        SolarPlantModel plant = SolarPlantModel.findById(plantId);

        //UserPowerBill powerBill = UserPowerBill.find("userId", user.userId).firstResult();
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.find("plantId",plantId).firstResult();

        String folder = documentFolder+"generated/documents/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(mandate_billing_net.data("plant", plant).data("user", user).data("address", address).data("powerBill", powerBill).render(), "", false);
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_vollmacht_netzbetreiber_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                if (generate == false) {
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", folder + newFilename + ".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", folder+newFilename+".pdf");

                    System.out.println("vollmacht_netzbetreiber_ | PDF create Status: " + status);

                    File fileDownload = new File(folder + newFilename+".pdf");
                    Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                    response.header("Content-Disposition", "attachment;filename=" + "contract_energy_saving.pdf");
                    return response.build();
                } else {
                    String destinationFolder = documentFolder+"uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("vollmacht_netzbetreiber_ | PDF create Status: "+status);


                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=291", plantId).firstResult();

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
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();

            return response.build();
        }
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("sepa/{plantId}/{generate}")
    @Transactional
    public Response generateSepa(@PathParam("plantId") UUID plantId, @PathParam("generate") Boolean generate) {
        ///A_H-solar.family SEPA Lastschriftmandat V1.0_20210403

        System.out.println("-------------------SEPA START-----------------------");

        ProjectUserModel project = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);

        //this should be optimized in next phase
        if(securityIdentity.getRoles().contains("user")) {
            if (user.userId.compareTo(UUID.fromString(token.getSubject())) != 0) {
                return Response.status(403).build();
            }
        }

        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();
        UserSepaPermissionModel sepaData = UserSepaPermissionModel.find("userId", user.userId).firstResult();
        //  SolarPlantModel plant = SolarPlantModel.findById(plantId);

        Integer userNo = user.customerNo+100000;
        String sepaId = "0"+userNo.toString();

        String folder = documentFolder+"generated/documents/";
        String doc =  htmlDocumentGenerator.generateHtmlDoc(
            sepa
                .data("sepaId", sepaId)
                .data("user", user)
                .data("address", address)
                .data("sepa", sepaData)
                .render()
        );
        try {
            String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_sepa_"+UUID.randomUUID().toString();
            newFilename = newFilename.replaceAll(" ", "_");

            HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                if (generate == false) {
                    return Response.status(403).build();
                } else {
                    String destinationFolder = documentFolder + "uploads/";
                    //ubuntu server
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///" + folder + newFilename + ".html", destinationFolder + newFilename + ".pdf");

                    System.out.println("sepa | PDF create Status: " + status);

                    FileContainerModel fc = FileContainerModel.find("contextId=?1 and contextType=2 and type=201", plantId).firstResult();

                    //save file to db
                    FileModel file = new FileModel();
                    file.fileName = newFilename + ".pdf";
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

            /*
            if (fileHelper.saveToDisk(folder,"sepa.html", doc) == true) {
                System.out.println("sepa | Html file created");

                //don't use injection as it will be an external function
                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + "sepa.html", folder+"sepa.pdf");

                System.out.println("sepa | PDF create Status: "+status);
                File fileDownload = new File(folder + "sepa.pdf");
                Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                response.header("Content-Disposition", "attachment; filename=" + "sepa_"+user.lastName+"_"+user.firstName+".pdf");
                return response.build();
            } else {
                Response.ResponseBuilder response = Response.serverError();
                return response.build();
            }
             */
        } catch (Exception e) {
            System.out.println(e);
            Response.ResponseBuilder response = Response.serverError();
            return response.build();
        }
    }
}
