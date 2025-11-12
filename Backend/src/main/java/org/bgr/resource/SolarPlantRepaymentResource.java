package org.bgr.resource;

import com.fasterxml.jackson.databind.node.ObjectNode;
import io.quarkus.panache.common.Sort;
import io.quarkus.qute.Template;
import org.bgr.helper.*;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.UserAddressModel;
import org.bgr.model.db.*;
import org.bgr.service.MailService;
import org.bgr.service.SolarPlantRepaymentService;
import org.eclipse.microprofile.config.inject.ConfigProperty;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.Response;
import java.io.File;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.UUID;
import java.util.concurrent.TimeUnit;

import static java.lang.Math.abs;

@ApplicationScoped
@Path("plant-repayment")
public class SolarPlantRepaymentResource {
    @ConfigProperty(name = "app.folder.documents")
    String documentFolder;

    @Inject
    HtmlDocumentGenerator htmlDocumentGenerator;

    @Inject
    FileHelper fileHelper;

    @Inject
    MailService mailService;

    @Inject
    Template plant_repayment;

    @Inject
    Template plant_repayment_reminder;

    @Inject
    Template service_fee;

    @Inject
    SolarPlantRepaymentService repaymentService;

    @GET
    @RolesAllowed({"manager", "admin"})
    //@RolesAllowed({"admin"})
    @Path("list")
    @Transactional
    public SolarPlantRepaymentResult getSolarPlantList(@QueryParam("page") Integer page, @QueryParam("perPage") Integer perPage, @QueryParam("sortBy") String sortBy,
                                                       @QueryParam("sortDesc") Boolean descending, @QueryParam("q") String q, @QueryParam("status") Integer status,
                                                       @QueryParam("calculationYear") Integer calculationYear, @QueryParam("calculationYearPeriod") Integer calculationYearPeriod) {

        //todo -- contractFilesChecked is used as filter // needs to be updated
        return new SolarPlantRepaymentResult(200,
                SolarPlantModel.count("(rs IS NULL OR rs < 99) and plantInUse is true"),
                SolarPlantModel.listSolarPlantRepayment(sortBy, descending, page, perPage, q, status, calculationYear, calculationYearPeriod));
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    //@RolesAllowed({"admin"})
    @Path("stats")
    @Transactional
    public ObjectNode getSolarPlantList(@QueryParam("calculationYear") Integer calculationYear, @QueryParam("calculationYearPeriod") Integer calculationYearPeriod) {
        System.out.println(calculationYear);
        System.out.println(calculationYearPeriod);

        return repaymentService.getStatsForPeriod(calculationYear, calculationYearPeriod);
    }

    @POST
    @RolesAllowed({"admin"})
    @Path("add-edit")
    @Transactional
    public ResultCommonObject addRepaymentDetail(SolarPlantRepaymentData repaymentDataReq) {
        if (repaymentDataReq.id != null) {
            SolarPlantRepaymentData repaymentData = SolarPlantRepaymentData.findById(repaymentDataReq.id);

            //defaults
            repaymentData.rs = 1;
            repaymentData.processed = false;

            repaymentData.consumptionTariff = repaymentDataReq.consumptionTariff;
            repaymentData.powerConsumption = repaymentDataReq.powerConsumption;
            repaymentData.powerProduction = repaymentDataReq.powerProduction;
            repaymentData.productionExtraTariff = repaymentDataReq.productionExtraTariff;
            repaymentData.productionTariff = repaymentDataReq.productionTariff;
            repaymentData.repaymentPeriod = repaymentDataReq.repaymentPeriod;
            repaymentData.repaymentFromDate = repaymentDataReq.repaymentFromDate;
            repaymentData.repaymentToDate = repaymentDataReq.repaymentToDate;

            repaymentData.persist();
        } else {
            System.out.println("insert");

            repaymentDataReq.rs = 1;
            repaymentDataReq.processed = false;
            repaymentDataReq.persist();
        }

        /*
        SolarPlantRepaymentData repaymentData = new SolarPlantRepaymentData();
        //move to service when details are known!
        repaymentData.rs = 1;
        repaymentData.processed = false;
        repaymentData.persist();
         */
        return new ResultCommonObject(200, "OK");
    }


    @GET
    @RolesAllowed({"admin"})
    @Path("process-repayment-data")
    @Transactional
    public void processRepayment() {
        // !!!!!!!! THE MODELS ARE TO BE DEFINED, HERE ARE ONLY PLACEHOLDERS

        System.out.println("process repayment");
        String folder = documentFolder+"generated/repayment/";

        //temp clear table
        //SolarPlantRepaymentLog.deleteAll();

        List<SolarPlantRepaymentData> repaymentDataList = SolarPlantRepaymentData.list("processed=?1 and rs=?2", Sort.by("t0").ascending(), false,1);
        for (SolarPlantRepaymentData repayment :repaymentDataList) {
            System.out.println(repayment.plantId);


            SolarPlantModel plant = SolarPlantModel.findById(repayment.plantId);
            System.out.println(plant.unitPrice);

            ProjectUserModel project = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);
            UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

            System.out.println(user.firstName);

            Double amount = plant.unitPrice/12.5;
            Double amountToPay = plant.unitPrice/12.5;

            Double amountProduction = 0.0;
            amountProduction += repayment.productionTariff * repayment.powerProduction;
            amountProduction += repayment.productionExtraTariff * repayment.powerProduction;
            amountProduction -= repayment.consumptionTariff * repayment.powerConsumption;

            //we need logic
            amount = abs(amountProduction - amount);

            Double notUsedPower = (repayment.powerProduction-repayment.powerConsumption) * repayment.productionTariff;
            Double usedPower = repayment.powerConsumption * repayment.consumptionTariff;
            Double sumPower = notUsedPower+usedPower;

            //temp fake
            Double baseAmount = 3276.77;

            Double sumNew = baseAmount - sumPower;

            String doc =  htmlDocumentGenerator.generateHtmlDoc(
                    plant_repayment
                            .data("user", user)
                            .data("address", address)
                            .data("plant", plant)
                            .data("amount", amount)
                            .data("repayment", repayment)
                            .data("date", LocalDate.now().format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("dateFrom", repayment.repaymentFromDate.format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("dateTo", repayment.repaymentToDate.format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("plantPower", GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2))
                            .data("powerProduction", GermanNumberParser.getGermanNumberFormat(repayment.powerProduction,2))
                            .data("powerConsumption", GermanNumberParser.getGermanNumberFormat(repayment.powerConsumption,2))
                            .data("powerDiff", GermanNumberParser.getGermanNumberFormat(repayment.powerProduction-repayment.powerConsumption,2))
                            .data("powerDiffPercent", GermanNumberParser.getGermanNumberFormat(repayment.powerConsumption/repayment.powerProduction,2))
                            .data("productionTariff", GermanNumberParser.getGermanNumberFormat(repayment.productionTariff,4))
                            .data("consumptionTariff", GermanNumberParser.getGermanNumberFormat(repayment.consumptionTariff,4))
                            .data("notUsedPower", GermanNumberParser.getGermanNumberFormat(notUsedPower,2))
                            .data("usedPower", GermanNumberParser.getGermanNumberFormat(usedPower,2))
                            .data("sumPower", GermanNumberParser.getGermanNumberFormat(sumPower,2))
                            .data("baseAmount", GermanNumberParser.getGermanNumberFormat(baseAmount,2))
                            .data("sumNew", GermanNumberParser.getGermanNumberFormat(sumNew,2))

                            .render()
                    ,plant.title
            );

            //System.out.println(doc);

            try{
                ProjectUserModel pu = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
                String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_abrechnung_"+repayment.repaymentPeriod;

                newFilename = newFilename.replaceAll(" ", "_");
                newFilename = newFilename.replaceAll("/", "_");

                if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                    System.out.println("repayment_ | Html file created");

                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("repayment_ | PDF create Status: "+status);

                    //todo some checks
                    if (status == true) {
                        // try sending mail
                        ResultCommonObject mailStatus = mailService.sendRepaymentMail(folder+newFilename+".pdf",newFilename+".pdf", pu.userId);

                        Boolean mailSentToCustomer = false;
                        if (mailStatus.getStatus() == 200) {
                            mailSentToCustomer = true;
                        }

                        SolarPlantRepaymentLog repaymentLog = new SolarPlantRepaymentLog();
                        repaymentLog.repaymentDataId = repayment.id;
                        repaymentLog.plantId = repayment.plantId;
                        repaymentLog.amount = amount;
                        repaymentLog.amountToPay = amountToPay;
                        repaymentLog.amountProduction = amountProduction;
                        repaymentLog.repaymentPeriod = repayment.repaymentPeriod;
                        repaymentLog.datumGenerated = LocalDateTime.now();
                        repaymentLog.datumCustomerMailSent = LocalDateTime.now();
                        repaymentLog.documentName = newFilename+".pdf";
                        repaymentLog.customerMailSent = mailSentToCustomer;
                        repaymentLog.rs = 1;

                        repaymentLog.persist();
                    }
                }

            } catch (Exception e) {
                System.out.println(e);
            }
        }
    }


    //test phase
    @GET
    @RolesAllowed({"admin"})
    @Path("process-repayment-data-single/{plantId}")
    @Transactional
    public Response processRepaymentPerPlant(@PathParam("plantId") UUID plantId) {
        // !!!!!!!! THE MODELS ARE TO BE DEFINED, HERE ARE ONLY PLACEHOLDERS

        System.out.println("process repayment");
        String folder = documentFolder+"generated/repayment/";

        //temp clear table
        //SolarPlantRepaymentLog.deleteAll();

        List<SolarPlantRepaymentData> repaymentDataList = SolarPlantRepaymentData.list("processed=?1 and rs=?2 and plantId=?3", Sort.by("t0").ascending(), false,1, plantId);
        for (SolarPlantRepaymentData repayment :repaymentDataList) {
            System.out.println(repayment.plantId);


            SolarPlantModel plant = SolarPlantModel.findById(repayment.plantId);
            System.out.println(plant.unitPrice);

            ProjectUserModel project = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);
            UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

            System.out.println(user.firstName);

            Double amount = plant.unitPrice/12.5;
            Double amountToPay = plant.unitPrice/12.5;

            Double amountProduction = 0.0;
            amountProduction += repayment.productionTariff * repayment.powerProduction;
            amountProduction += repayment.productionExtraTariff * repayment.powerProduction;
            amountProduction -= repayment.consumptionTariff * repayment.powerConsumption;

            //we need logic
            amount = abs(amountProduction - amount);

            Double notUsedPower = (repayment.powerProduction-repayment.powerConsumption) * repayment.productionTariff;
            Double usedPower = repayment.powerConsumption * repayment.consumptionTariff;
            Double sumPower = notUsedPower+usedPower;

            //temp fake
            Double baseAmount = 3276.77;

            Double sumNew = baseAmount - sumPower;

            String doc =  htmlDocumentGenerator.generateHtmlDoc(
                    plant_repayment
                            .data("user", user)
                            .data("address", address)
                            .data("plant", plant)
                            .data("amount", amount)
                            .data("repayment", repayment)
                            .data("date", LocalDate.now().format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("dateFrom", repayment.repaymentFromDate.format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("dateTo", repayment.repaymentToDate.format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                            .data("plantPower", GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2))
                            .data("powerProduction", GermanNumberParser.getGermanNumberFormat(repayment.powerProduction,2))
                            .data("powerConsumption", GermanNumberParser.getGermanNumberFormat(repayment.powerConsumption,2))
                            .data("powerDiff", GermanNumberParser.getGermanNumberFormat(repayment.powerProduction-repayment.powerConsumption,2))
                            .data("powerDiffPercent", GermanNumberParser.getGermanNumberFormat(repayment.powerConsumption/repayment.powerProduction,2))
                            .data("productionTariff", GermanNumberParser.getGermanNumberFormat(repayment.productionTariff,4))
                            .data("consumptionTariff", GermanNumberParser.getGermanNumberFormat(repayment.consumptionTariff,4))
                            .data("notUsedPower", GermanNumberParser.getGermanNumberFormat(notUsedPower,2))
                            .data("usedPower", GermanNumberParser.getGermanNumberFormat(usedPower,2))
                            .data("sumPower", GermanNumberParser.getGermanNumberFormat(sumPower,2))
                            .data("baseAmount", GermanNumberParser.getGermanNumberFormat(baseAmount,2))
                            .data("sumNew", GermanNumberParser.getGermanNumberFormat(sumNew,2))

                            .render()
                    ,plant.title
            );

            //System.out.println(doc);

            try{
                ProjectUserModel pu = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
                String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_abrechnung_"+repayment.repaymentPeriod;

                newFilename = newFilename.replaceAll(" ", "_");
                newFilename = newFilename.replaceAll("/", "_");

                if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                    System.out.println("repayment_ | Html file created");

                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("repayment_ | PDF create Status: "+status);

                    //todo some checks
                    if (status == true) {
                        // try sending mail
                        ResultCommonObject mailStatus = mailService.sendRepaymentMail(folder+newFilename+".pdf",newFilename+".pdf", pu.userId);

                        Boolean mailSentToCustomer = false;
                        if (mailStatus.getStatus() == 200) {
                            mailSentToCustomer = true;
                        }

                        SolarPlantRepaymentLog repaymentLog = new SolarPlantRepaymentLog();
                        repaymentLog.repaymentDataId = repayment.id;
                        repaymentLog.plantId = repayment.plantId;
                        repaymentLog.amount = amount;
                        repaymentLog.amountToPay = amountToPay;
                        repaymentLog.amountProduction = amountProduction;
                        repaymentLog.repaymentPeriod = repayment.repaymentPeriod;
                        repaymentLog.datumGenerated = LocalDateTime.now();
                        repaymentLog.datumCustomerMailSent = LocalDateTime.now();
                        repaymentLog.documentName = newFilename+".pdf";
                        repaymentLog.customerMailSent = mailSentToCustomer;
                        repaymentLog.rs = 1;

                        repaymentLog.persist();

                        repayment.processed = true;
                        repayment.persist();
                    }
                }
            } catch (Exception e) {
                System.out.println(e);
            }
        }

        //temp return
        return Response.ok().build();
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("delete-log/{plantId}")
    @Transactional
    public Response deleteLog(@PathParam("plantId") UUID plantId) {
        SolarPlantRepaymentLog.delete("plantId=?1", plantId);
        SolarPlantRepaymentData.update("processed=false where plantId=?1", plantId);

        return Response.ok().build();
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("service-operation/{plantId}")
    @Transactional
    public Response serviceOperation(@PathParam("plantId") UUID plantId) {
        // !!!!!!!! THE MODELS ARE TO BE DEFINED, HERE ARE ONLY PLACEHOLDERS

        System.out.println("process service operation fee");
        String folder = documentFolder+"generated/service_fee/";

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        System.out.println(plant.unitPrice);

        ProjectUserModel project = ProjectUserModel.find("plantId", plantId).firstResult();
        UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);
        UserAddressModel address = UserAddressModel.find("userId", user.userId).firstResult();

        Double serviceFee = 36.67;
        Double serviceFeeBrutto = serviceFee * 1.2;
        Double serviceFeeTax = serviceFeeBrutto - serviceFee;

        String doc =  htmlDocumentGenerator.generateHtmlDoc(
                service_fee
                        .data("user", user)
                        .data("address", address)
                        .data("plant", plant)
                        .data("date", LocalDate.now().format(DateTimeFormatter.ofPattern("dd.MM. yyyy")))
                        .data("plantPower", GermanNumberParser.getGermanNumberFormat(plant.nominalPower,2))
                        //.data("serviceFee", GermanNumberParser.getGermanNumberFormat(plant.serviceFee,2))
                        .data("serviceFeeBrutto", GermanNumberParser.getGermanNumberFormat(serviceFeeBrutto,2))
                        .data("serviceFeeTax", GermanNumberParser.getGermanNumberFormat(serviceFeeTax,2))
                        .data("serviceFee", GermanNumberParser.getGermanNumberFormat(serviceFee,2))
                        .render()
                ,plant.title
        );

        //System.out.println(doc);

        try{
            ProjectUserModel pu = ProjectUserModel.find("plantId", plantId).firstResult();

            //String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_anschreiben_"+UUID.randomUUID().toString();
            String newFilename = "service_fee_"+UUID.randomUUID().toString();

            if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                System.out.println("service_fee_ | Html file created");

                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");


                File fileDownload = new File(folder + newFilename +".pdf");
                Response.ResponseBuilder response = Response.ok((Object) fileDownload, "application/pdf");

                response.header("Content-Disposition", "attachment;filename=" + newFilename +".pdf");
                return response.build();

            }

        } catch (Exception e) {
            System.out.println(e);
            return Response.status(500).build();
        }

        return Response.status(404).build();
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("process-repayment-data/{id}")
    @Transactional
    public void processPayments(@PathParam("id") UUID id) {
        //later api call with some data// id for now to simulate updates
        SolarPlantRepaymentLog repayment = SolarPlantRepaymentLog.findById(id);

        repayment.txId = UUID.randomUUID().toString();
        repayment.paymentVerified = true;
        repayment.datumPaid = LocalDateTime.now();

        repayment.persist();

        //send mail
        ProjectUserModel pu = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
        mailService.sendRepaymentMailConfirm(pu.userId);
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("process-repayment-multiple/{idPlant}/{calculationYear}/{calculationYearPeriod}")
    @Transactional
    public void processMultiplePayments(@PathParam("idPlant") UUID idPlant, @PathParam("calculationYear") Integer calculationYear, @PathParam("calculationYearPeriod") Integer calculationYearPeriod) {
        //later api call with some data// id for now to simulate updates

        List<String> res = repaymentService.getPlantRepaymentsByPeriod(idPlant, calculationYear, calculationYearPeriod);

        for (String repaymentId : res) {
            System.out.println(repaymentId);
            processPayments(UUID.fromString(repaymentId));
        }
    }

    @POST
    //@RolesAllowed({"admin"})
    @Path("repayment-data/")
    //skip for now
    @RolesAllowed({"manager", "admin"})
    @Transactional
    public ResultCommonObject processRepaymentData(SolarPlantRepaymentData repayment) {
        repayment.processed = false;
        repayment.rs = 1;
        repayment.persist();

        return new ResultCommonObject(200, "OK");
    }

    //delete after test
    @POST
    //@RolesAllowed({"admin"})
    @Path("repayment-log/")
    //skip for now
    @RolesAllowed({"manager", "admin"})
    @Transactional
    public ResultCommonObject processRepaymentLog(SolarPlantRepaymentLog repayment) {

        return new ResultCommonObject(200, "OK");
    }

    @GET
    //@RolesAllowed({"admin"})
    @Path("process-repayment-log-reminders/")
    @RolesAllowed({"manager", "admin"})
    @Transactional
    public Response processRepaymentLogReminders() {
        System.out.println("Process Repayment Log Reminders");

        //temp clear table
        //SolarPlantRepaymentLogReminder.deleteAll();

        //to be defined
        //add reminder created to solarpaymnetlog
        //etc...
        //List<SolarPlantRepaymentLog> repaymentList = SolarPlantRepaymentLog.find("datumGenerated > ?1 and datumPaid is NULL", LocalDateTime.now().minusDays(7)).list();
        List<SolarPlantRepaymentLog> repaymentList = SolarPlantRepaymentLog.find("datumPaid is NULL").list();
        String folder = documentFolder+"generated/repayment/";

        for (SolarPlantRepaymentLog repayment : repaymentList) {
            ProjectUserModel pu = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            ProjectUserModel project = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);

            Integer nrReminders = (int) SolarPlantRepaymentLogReminder.count("repaymentLogId", repayment.id);

            SolarPlantRepaymentLogReminder reminder =  new SolarPlantRepaymentLogReminder();

            //temp add 10 eur as reminder cost
            reminder.amount = repayment.amount + 10*nrReminders;

            reminder.repaymentLogId = repayment.id;
            reminder.rs = 1;
            reminder.reminderNr = nrReminders+1;

            String doc =  htmlDocumentGenerator.generateHtmlDoc(
                plant_repayment_reminder
                    .data("amount", reminder.amount)
                    .render()
                ,""
            );

            try {
                //String newFilename = "repayment_"+UUID.randomUUID().toString();

                String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_abrechnung_"+repayment.repaymentPeriod+"_mahnung_"+reminder.reminderNr;
                newFilename = newFilename.replaceAll(" ", "_");
                newFilename = newFilename.replaceAll("/", "_");

                if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                    System.out.println("repayment_reminder_ | Html file created");

                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("repayment_reminder_ | PDF create Status: "+status);

                    if (mailService.sendRepaymentReminderMail(folder+newFilename+".pdf", newFilename+".pdf", pu.userId).getStatus() == 200) {
                        reminder.documentName = newFilename+".pdf";
                        reminder.datumGenerated = LocalDateTime.now();
                        reminder.customerMailSent = true;
                        reminder.persist();


                    }
                }
            } catch (Exception e) {
                System.out.println(e);
            }
        }
        return Response.ok().build();
    }

    //test pase
    @GET
    //@RolesAllowed({"admin"})
    @Path("process-repayment-log-reminders-single/{plantId}")
    @RolesAllowed({"manager", "admin"})
    @Transactional
    public Response processRepaymentLogRemindersSingle(@PathParam("plantId") UUID plantId) {
        System.out.println("Process Repayment Log Reminders");

        //temp clear table
        //SolarPlantRepaymentLogReminder.deleteAll();

        //to be defined
        //add reminder created to solarpaymnetlog
        //etc...
        //List<SolarPlantRepaymentLog> repaymentList = SolarPlantRepaymentLog.find("datumGenerated > ?1 and datumPaid is NULL", LocalDateTime.now().minusDays(7)).list();
        List<SolarPlantRepaymentLog> repaymentList = SolarPlantRepaymentLog.find("datumPaid is NULL and plantId = ?1", plantId).list();
        String folder = documentFolder+"generated/repayment/";

        for (SolarPlantRepaymentLog repayment : repaymentList) {
            ProjectUserModel pu = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            ProjectUserModel project = ProjectUserModel.find("plantId", repayment.plantId).firstResult();
            UserBasicInfoModel user = UserBasicInfoModel.findById(project.userId);

            Integer nrReminders = (int) SolarPlantRepaymentLogReminder.count("repaymentLogId", repayment.id);

            SolarPlantRepaymentLogReminder reminder =  new SolarPlantRepaymentLogReminder();

            //temp add 10 eur as reminder cost
            reminder.amount = repayment.amount + 10*nrReminders;

            reminder.repaymentLogId = repayment.id;
            reminder.rs = 1;
            reminder.reminderNr = nrReminders+1;

            String doc =  htmlDocumentGenerator.generateHtmlDoc(
                    plant_repayment_reminder
                            .data("amount", reminder.amount)
                            .render()
                    ,""
            );

            try {
                //String newFilename = "repayment_"+UUID.randomUUID().toString();

                String newFilename = user.firstName.toLowerCase()+"_"+user.lastName.toLowerCase()+"_abrechnung_"+repayment.repaymentPeriod+"_mahnung_"+reminder.reminderNr;
                newFilename = newFilename.replaceAll(" ", "_");
                newFilename = newFilename.replaceAll("/", "_");

                if (fileHelper.saveToDisk(folder,newFilename+".html", doc) == true) {
                    System.out.println("repayment_reminder_ | Html file created");

                    HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                    Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + newFilename+".html", folder+newFilename+".pdf");

                    //windows server
                    //Boolean status = convertToPdf.convertHtmlFileToPdf("file://"+folder + newFilename+".html", destinationFolder+newFilename+".pdf");

                    System.out.println("repayment_reminder_ | PDF create Status: "+status);

                    if (mailService.sendRepaymentReminderMail(folder+newFilename+".pdf", newFilename+".pdf", pu.userId).getStatus() == 200) {
                        reminder.documentName = newFilename+".pdf";
                        reminder.datumGenerated = LocalDateTime.now();
                        reminder.customerMailSent = true;
                        reminder.persist();

                        TimeUnit.MILLISECONDS.sleep(1000);


                        repayment.hasReminders = true;
                        repayment.persist();

                        TimeUnit.MILLISECONDS.sleep(1000);
                    }
                }
            } catch (Exception e) {
                System.out.println(e);
            }
        }
        return Response.ok().build();
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("delete-repayment/{plantId}")
    @Transactional
    public Response deleteReminder(@PathParam("plantId") UUID plantId) {

        //SolarPlantRepaymentLog.delete("plantId=?1", plantId);
        //SolarPlantRepaymentData.update("processed=false where plantId=?1", plantId);

        List<SolarPlantRepaymentLog> repaymentList = SolarPlantRepaymentLog.find("plantId = ?1", plantId).list();
        for (SolarPlantRepaymentLog repayment : repaymentList) {
            System.out.println(repayment.id);
            SolarPlantRepaymentLogReminder.delete("repaymentLogId=?1", repayment.id);

            repayment.hasReminders = false;
            repayment.persist();
        }

        return Response.ok().build();
    }


    @GET
    @RolesAllowed({"admin"})
    @Path("get-repayment-log/{plantId}")
    @Transactional
    public List<SolarPlantRepaymentLog> getRepaymentLog(@PathParam("plantId") UUID plantId) {
        List<SolarPlantRepaymentLog> repaymentLog = SolarPlantRepaymentLog.list("plantId=?1 and rs=1",Sort.by("t0").descending(), plantId);

        return repaymentLog;
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("get-reminder-log/{repaymentLogId}")
    @Transactional
    public List<SolarPlantRepaymentLogReminder> getReminderLog(@PathParam("repaymentLogId") UUID repaymentLogId) {
        List<SolarPlantRepaymentLogReminder> reminderLog = SolarPlantRepaymentLogReminder.list("repaymentLogId=?1 and rs=1",Sort.by("t0").descending(), repaymentLogId);

        return reminderLog;
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("get-repayment-data/{plantId}")
    @Transactional
    public List<SolarPlantRepaymentData> getRepaymentData(@PathParam("plantId") UUID plantId) {
        List<SolarPlantRepaymentData> repaymentData = SolarPlantRepaymentData.list("plantId=?1 and rs=1",Sort.by("t0").descending(), plantId);

        return repaymentData;
    }

    @GET
    @RolesAllowed({"admin"})
    @Path("repayment-reminder-list/{repaymentLogId}")
    @Transactional
    public List<SolarPlantRepaymentLogReminder> getRepaymentReminderData(@PathParam("repaymentLogId") UUID repaymentLogId) {
        System.out.println(repaymentLogId);
        List<SolarPlantRepaymentLogReminder> repaymentReminderData = SolarPlantRepaymentLogReminder.list("repaymentLogId=?1 and rs=1",Sort.by("t0").descending(), repaymentLogId);

        return repaymentReminderData;
    }

    class SolarPlantRepaymentResult extends ResultHelper {
        public long records = -1;

        public SolarPlantRepaymentResult(Integer status, Long records, List<ObjectNode> plantList) {
            super();
            this.records = records;
            this.status = status;
            this.payload = plantList;
        }

        public SolarPlantRepaymentResult(Integer status, List<SolarPlantModel> plantList) {
            super();
            this.status = status;
            this.payload = plantList;
        }
    }
}
