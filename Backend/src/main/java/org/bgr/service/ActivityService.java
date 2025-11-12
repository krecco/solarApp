package org.bgr.service;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.JsonMappingException;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import io.quarkus.security.identity.SecurityIdentity;
import org.bgr.model.SchedulerModel;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.db.ActivityModel;
import org.bgr.model.db.FileContainerModel;
import org.bgr.model.db.UserBasicInfoModel;
import org.bgr.model.db.WebInfoModel;
import org.eclipse.microprofile.jwt.JsonWebToken;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.UUID;

@ApplicationScoped
public class ActivityService {

    @Inject
    JsonWebToken token;

    @Inject
    SecurityIdentity securityIdentity;

    @Transactional
    public boolean processActivity(ActivityModel activity, String type) {
        try {
            //get the user that triggered the event
            activity.createdBy = getActivityCreator();
            activity.createdById = getActivityCreatorId();
            activity.rs  = 0;

            //process types
            switch(type) {
                case "user_register_web":
                    activity = processFrontendUserCreate(activity);
                    break;

                case "user_register_backend":
                    activity = processBackendUserCreate(activity);
                    break;

                case "file_upload":
                    activity = processFileUpload(activity);
                    break;
                case "file_delete":
                    activity = processFileDelete(activity);
                    break;
                case "file_status_change":
                    activity = processFileStatusChge(activity);
                    break;
                case "web_info":
                    activity = processWebInfo(activity);
                    break;
                case "event-plant":
                    activity = processEventPlant(activity);
                    break;
                case "frontend-msg":
                    activity = processFrontendMessages(activity);
                    break;
            }

            //insert to db
            activity.persist();

            return true;
        } catch (Exception e) {
            return false;
        }
    }

    private ActivityModel processEventPlant(ActivityModel activity) {
        SolarPlantModel plant = SolarPlantModel.findById(activity.contentId);

        activity.showOnUserDashboard = true;
        activity.notificationType = "primary";

        if ((activity.title.equals("cancel_by_customer")) || (activity.title.equals("delete_plant"))) {
            activity.notificationType = "danger";
        }


        activity.title = getEventPlantTitle(plant, activity.title);
        activity.content = "PV-Anlage: "+plant.title +" ("+getActivityCreator()+")";

        activity.rs = 1;
        return activity;
    }

    private ActivityModel processFileUpload(ActivityModel activity) {
        FileContainerModel fcm = FileContainerModel.findById(activity.contentId);
        String schedulerContent = "";

        //user files -- contextId == userId
        if (fcm.contextType == 1) {
            activity.contentType = "file-user";
            //activity.content = getFileTitle(fcm.type) + " wurde von "+getActivityCreator()+" hochgeladen.";
            //schedulerContent = getFileTitle(fcm.type) + " wurde von "+getActivityCreator()+" hochgeladen.";

            activity.content = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" hochgeladen.";
            schedulerContent = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" hochgeladen.";

        }

        //power plant -- contextId == powerPlantId
        if ((fcm.contextType == 2) || (fcm.contextType == 3) || (fcm.contextType == 4)) {
            activity.contentType = "file-plant";

            if (fcm.contextType == 4) {
                activity.contentType = "file-investment";
            }
            //activity.content = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator() + " zu " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") hochgeladen.";
            //schedulerContent = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator() + " zu " + getPlantTitle(activity.parentContentId) + " hochgeladen.";

            activity.content = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" hochgeladen.";
            schedulerContent = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" hochgeladen.";
        }

        if (activity.title == "Modified") {
            //activity.title = getFileTitle(fcm.type) + " Dokument wurde geändert.";
            activity.title = "Datei ("+getFileTitle(fcm.type)+") wurde hochgeladen.";
        } else {
            activity.title = "Datei ("+getFileTitle(fcm.type)+") wurde hochgeladen.";
            //activity.title = getFileTitle(fcm.type) + " Datei wurde hochgeladen.";
        }

        activity.showOnUserDashboard = true;
        activity.notificationType = "info";

        //if (!activity.userId.equals(activity.createdById)) {
        if(!securityIdentity.getRoles().contains("user")) {
            activity.rs = 1;

            //add activity to scheduler
            SchedulerModel schedule =  new SchedulerModel();
            schedule.rs = 0;
            schedule.function = "activityMailNotification";
            schedule.contextId = activity.userId;

            List<String> arguments = new ArrayList<>();
            arguments.add("fileUpload");
            arguments.add(schedulerContent);
            schedule.arguments = arguments;

            schedule.persist();
        }

        return activity;
    }

    private ActivityModel processFileDelete(ActivityModel activity) {
        FileContainerModel fcm = FileContainerModel.findById(activity.contentId);
        String schedulerContent = "";

        //user files -- contextId == userId
        if (fcm.contextType == 1) {
            activity.contentType = "file-user";
            //activity.content = getFileTitle(fcm.type) + " has been deleted from " + getCreatedEntityUserFullNameById(activity.userId) + " account  by " + getActivityCreator()+".";
            //schedulerContent = getFileTitle(fcm.type) + " has been deleted by " + getActivityCreator()+".";

            //activity.content = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator() + " gelöscht.";
            //schedulerContent = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator()+" gelöscht.";

            activity.content = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" gelöscht.";
            schedulerContent = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" gelöscht.";
        }

        //power plant -- contextId == powerPlantId
        if ((fcm.contextType == 2) || (fcm.contextType == 3) || (fcm.contextType == 4)) {
            activity.contentType = "file-plant";

            if (fcm.contextType == 4) {
                activity.contentType = "file-investment";
            }

            //activity.content = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator() + " aus " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") gelöscht.";
            //schedulerContent = getFileTitle(fcm.type) + " Datei wurde von " + getActivityCreator() + " aus " + getPlantTitle(activity.parentContentId) + " gelöscht.";

            activity.content = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" gelöscht.";
            schedulerContent = "Datei ("+activity.filename+") wurde von "+getActivityCreator()+" gelöscht.";
        }


        activity.title = "Datei ("+getFileTitle(fcm.type)+") wurde gelöscht.";

        activity.showOnUserDashboard = true;
        activity.notificationType = "danger";

        //if (!activity.userId.equals(activity.createdById)) {
        if(!securityIdentity.getRoles().contains("user")) {
            activity.rs = 1;

            //add activity to scheduler
            SchedulerModel schedule =  new SchedulerModel();
            schedule.rs = 0;
            schedule.function = "activityMailNotification";
            schedule.contextId = activity.userId;

            List<String> arguments = new ArrayList<>();
            arguments.add("fileDelete");
            arguments.add(schedulerContent);
            schedule.arguments = arguments;

            schedule.persist();
        }

        return activity;
    }

    private ActivityModel processFileStatusChge(ActivityModel activity) {
        FileContainerModel fcm = FileContainerModel.findById(activity.contentId);
        String schedulerContent = "";

        System.out.println("process file status chagne");

        activity.rs = 1;
        activity.title = "Dateistatus hat sich geändert";

        if (fcm.contextType == 1) {
            activity.contentType = "file-user-status";
            //activity.content = "Status of " + getFileTitle(fcm.type) + " for user " + getCreatedEntityUserFullNameById(activity.userId) + "has been changed to " + getFileStatus(fcm.rs) + "  by " + getActivityCreator() + ".";
            //schedulerContent = "Status of " + getFileTitle(fcm.type) + "has been changed to " + getFileStatus(fcm.rs) + " by " + getActivityCreator() + ".";

            activity.content = getFileTitle(fcm.type) + " Status  wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
            schedulerContent = getFileTitle(fcm.type) + " Status  wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
        }


        //power plant -- contextId == powerPlantId
        if ((fcm.contextType == 2) || (fcm.contextType == 3)) {
            activity.contentType = "file-plant-status";

            //activity.content = "Status of " +  getFileTitle(fcm.type) + " for solar plant " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") has been changed to " + getFileStatus(fcm.rs) + " by " + getActivityCreator()+".";
            //schedulerContent = "Status of " + getFileTitle(fcm.type) + " for solar plant " + getPlantTitle(activity.parentContentId) + " has been changed to " + getFileStatus(fcm.rs) + " by " + getActivityCreator() + ".";

            activity.content = getFileTitle(fcm.type) + " Status für " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
            schedulerContent = getFileTitle(fcm.type) + " Status für " + getPlantTitle(activity.parentContentId) + " wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
        }

        if (fcm.contextType == 4) {
            activity.contentType = "file-investment-status";

            //activity.content = "Status of " +  getFileTitle(fcm.type) + " for solar plant " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") has been changed to " + getFileStatus(fcm.rs) + " by " + getActivityCreator()+".";
            //schedulerContent = "Status of " + getFileTitle(fcm.type) + " for solar plant " + getPlantTitle(activity.parentContentId) + " has been changed to " + getFileStatus(fcm.rs) + " by " + getActivityCreator() + ".";

            //todo change content
            activity.content = getFileTitle(fcm.type) +" Status auf "+ getFileStatus(fcm.rs) + " geändert.";

            //activity.content = getFileTitle(fcm.type) + " Status für " + getPlantTitle(activity.parentContentId) + "(" + getCreatedEntityUserFullNameById(activity.userId) + ") wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
            //schedulerContent = getFileTitle(fcm.type) + " Status für " + getPlantTitle(activity.parentContentId) + " wurde von "+  getActivityCreator() + " auf "+ getFileStatus(fcm.rs) + " geändert.";
        }

        activity.notificationType = "info";
        activity.showOnUserDashboard = true;
        //if (activity.userId != activity.createdById) {
        if(!securityIdentity.getRoles().contains("user")) {
            activity.rs = 1;

            //add activity to scheduler
            SchedulerModel schedule =  new SchedulerModel();
            schedule.rs = 0;
            schedule.function = "activityMailNotification";
            schedule.contextId = activity.userId;

            List<String> arguments = new ArrayList<>();
            arguments.add("fileStatusChange");
            arguments.add(schedulerContent);
            schedule.arguments = arguments;

            schedule.persist();
        }

        return activity;
    }

    private ActivityModel processFrontendUserCreate(ActivityModel activity) {
        activity.title = "Benutzer registriert!";
        activity.content = getCreatedEntityUserFullNameById(activity.contentId)+ " hat sich bei solar.family registriert.";
        activity.showOnUserDashboard = true;
        activity.contentType = "user";
        activity.notificationType = "success";

        return activity;
    }

    private ActivityModel processBackendUserCreate(ActivityModel activity) {
        activity.rs = 1;
        activity.title = "Benutzer registriert!";
        activity.content = getCreatedEntityUserFullNameById(activity.contentId)+ " wurde zu solar.family hinzugefügt (" + activity.createdBy + ").";
        activity.showOnUserDashboard = true;
        activity.contentType = "user";
        activity.notificationType = "info";

        return activity;
    }

    private ActivityModel processWebInfo(ActivityModel activity) {
        System.out.println("**********************************************************************************************************************");

        try {

            WebInfoModel info = WebInfoModel.findById(activity.contentId);
            DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd.MM.YYYY H:m");
            StringBuilder content = new StringBuilder();

            content.append("Gewünschter Telefontermin: ");
            content.append(formatter.format(info.desiredDateTime));
            content.append("<br>Investor: ");

            if (info.isInvestor == true) {
                content.append("JA");
            } else {
                content.append("NEIN");
            }

            content.append("<br><br>Photovoltaik - Anlagerechner:<br>");

        try {
            ObjectMapper mapper = new ObjectMapper();
            JsonNode node = mapper.readTree(info.webCalculation);

            content.append("Stromverbrauch pro Jahr: "+node.get("powerusage").asText()+" kWh<br>");
            content.append("Objekt für die PV Anlage: "+node.get("propertytype").asText()+"<br>");
            content.append("Aktuelle Heizung: "+node.get("heatingtype").asText()+"<br>");
            content.append("Empfohlene Anlagengröße (kWp): "+node.get("systemsize").asText()+" kWp<br>");


        } catch (JsonMappingException e) {
            e.printStackTrace();
        } catch (JsonProcessingException e) {
            e.printStackTrace();
        }
            activity.rs = 1;
            activity.showOnUserDashboard = true;
            activity.contentType = "web-info";
            activity.notificationType = "info";
            activity.title = "Web Info";
            activity.content = content.toString();

        } catch (Exception e) {
            System.out.println("**********************************************************************************************************************");
            System.out.println(e);
            System.out.println("**********************************************************************************************************************");
        }

        return activity;

    }

    private ActivityModel processFrontendMessages(ActivityModel activity) {
        System.out.println("******************************************************processFrontendMessages****************************************************************");
        try {
            StringBuilder content = new StringBuilder();
            if (activity.title == "new_prepayment") {
                activity.title = "Anzahlungänderung";

                content.append(getActivityCreator() + " will Anzahlungänderung für PV-Anlage: "+getPlantTitle(activity.contentId)+" machen. Neue Anzahlung: "+ activity.content+" EUR");
                activity.content = content.toString();
            } else if (activity.title == "direct_buy") {
                activity.title = "Direktkauf anforderung";

                content.append(getActivityCreator() + " will die PV-Anlage: "+getPlantTitle(activity.contentId)+" direkt Kaufen. Kundennachricht: "+ activity.content);
                activity.content = content.toString();
            } else if (activity.title == "request_project_change") {
                activity.title = "Änderung an PV Projektierung";

                content.append(getActivityCreator() + " will Projektierung Änderung für die PV-Anlage: "+getPlantTitle(activity.contentId)+". Kundennachricht: "+ activity.content);
                activity.content = content.toString();
            } else if (activity.title == "request_contract_change") {
                activity.title = "Angebotsänderung";

                content.append(getActivityCreator() + " will Angebotsänderung für die PV-Anlage: "+getPlantTitle(activity.contentId)+". Kundennachricht: "+ activity.content);
                activity.content = content.toString();
            }


            activity.rs = 0;
            activity.showOnUserDashboard = true;
            activity.notificationType = "danger";

        } catch (Exception e) {
            System.out.println("****************************************************processFrontendMessages******************************************************************");
            System.out.println(e);
            System.out.println("*******************************************************processFrontendMessages***************************************************************");
        }
        /*
        try {

            WebInfoModel info = WebInfoModel.findById(activity.contentId);
            DateTimeFormatter formatter = DateTimeFormatter.ofPattern("dd.MM.YYYY H:m");
            StringBuilder content = new StringBuilder();

            content.append("Gewünschter Telefontermin: ");
            content.append(formatter.format(info.desiredDateTime));
            content.append("<br>Investor: ");

            if (info.isInvestor == true) {
                content.append("JA");
            } else {
                content.append("NEIN");
            }

            content.append("<br><br>Photovoltaik - Anlagerechner:<br>");

            try {
                ObjectMapper mapper = new ObjectMapper();
                JsonNode node = mapper.readTree(info.webCalculation);

                content.append("Stromverbrauch pro Jahr: "+node.get("powerusage").asText()+" kWh<br>");
                content.append("Objekt für die PV Anlage: "+node.get("propertytype").asText()+"<br>");
                content.append("Aktuelle Heizung: "+node.get("heatingtype").asText()+"<br>");
                content.append("Empfohlene Anlagengröße (kWp): "+node.get("systemsize").asText()+" kWp<br>");


            } catch (JsonMappingException e) {
                e.printStackTrace();
            } catch (JsonProcessingException e) {
                e.printStackTrace();
            }
            activity.rs = 1;
            activity.showOnUserDashboard = true;
            activity.contentType = "web-info";
            activity.notificationType = "info";
            activity.title = "Web Info";
            activity.content = content.toString();

        } catch (Exception e) {
            System.out.println("**********************************************************************************************************************");
            System.out.println(e);
            System.out.println("**********************************************************************************************************************");
        }

         */
        return activity;
    }

    private String getActivityCreator() {
        try {
            return token.getClaim("name").toString();
        } catch (Exception e) {
            return "";
        }
    }

    private UUID getActivityCreatorId() {
        return UUID.fromString(token.getSubject());
    }

    private String getCreatedEntityUserFullNameById(UUID id) {
        UserBasicInfoModel user = UserBasicInfoModel.findById(id);

        return user.firstName + " " + user.lastName;
    }

    private String getPlantTitle(UUID plantId) {
        SolarPlantModel plant = SolarPlantModel.findById(plantId);

        return plant.title;
    }

    private String getFileTitle(Integer type) {
        String fileTitle = "";
        switch(type) {
            case 11:
                fileTitle =  "Personalausweis / Reisepass";
                break;
            case 12:
                fileTitle =  "Vollmacht Abwicklung";
                break;
            case 13:
                fileTitle = "Vollmacht Energieabrechnung";
                break;
            case 14:
                fileTitle = "SEPA Lastschriftmandat";
                break;
            case 20:
                fileTitle = "Verbrauchsabrechnung";
                break;
            case 21:
                fileTitle = "Prognoserechnung";
                break;
            case 22:
                fileTitle = "Projektierung";
                break;
            case 23:
                fileTitle = "Anschreiben";
                break;
            case 24:
                fileTitle = "Energieeinsparung";
                break;
            case 25:
                fileTitle = "Verrechnungsblatt";
                break;
            case 26:
                fileTitle = "Bildergalerie";
                break;
            case 27:
                fileTitle = "Vollmacht Abwicklung";
                break;
            case 28:
                fileTitle = "Diverse Dokumente";
                break;
            case 29:
                fileTitle = "Vollmacht Energieabrechnung";
                break;
            case 201:
                fileTitle = "SEPA";
                break;
            case 291:
                fileTitle = "Vollmacht Netzbetreiber";
                break;
            case 30:
                fileTitle = "Dachfoto";
                break;
            case 31:
                fileTitle = "Wechselrichterfoto";
                break;
            case 32:
                fileTitle = "Kabelverlegungfoto";
                break;
            case 33:
                fileTitle = "Einspeisepunktfoto";
                break;
            case 34:
                fileTitle = "Warmwasserbereitungfoto";
                break;
            case 35:
                fileTitle = "Ladeinfrastrukturfoto";
                break;
            case 41:
                fileTitle = "Dokument-1";
                break;
            case 42:
                fileTitle = "Dokument-2";
                break;
            case 43:
                fileTitle = "Dokument-3";
                break;
        }

        return fileTitle;
    }

    private String getFileStatus(Integer status) {
        String fileStatus = "";
        switch(status) {
            case 1:
                fileStatus = "Neu";
                break;
            case 2:
                fileStatus = "Hochgeladen";
                break;
            case 3:
                fileStatus = "Geändert";
                break;
            case 4:
                fileStatus = "Überprüft";
                break;
        }

        return fileStatus;
    }

    private String getEventPlantTitle(SolarPlantModel plant, String event) {
        String dateTimeFormat = "dd.MM. yyyy hh:mm";
        String dateFormat = "dd.MM. yyyy";
        String eventTitle = "";

        System.out.println(event);

        switch (event) {
            case "user_register_mail_sent":
                eventTitle = "Begehungstermin vereinbart ( "+plant.inspectionCheckDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" | "+plant.inspectionMailBackendUserSendTo+" )";
                break;
            case "inspection_finish":
                eventTitle = "Begehungstermin durchgeführt ( "+plant.inspectionCheckFinishedDate.format(DateTimeFormatter.ofPattern(dateFormat))+" | "+plant.inspectionCheckFinishedMail+" )";
                break;
            case "calculation_sent_to_customer":
                eventTitle = "Planungsunterlagen erstellt und an Kunden übermittelt ( "+plant.calculationSentToCustomerDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
                break;
            case "order_interest_accepted":
                eventTitle = "Auftragsabsicht ( "+plant.orderInterestAcceptedDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
                break;
            case "contracts_sent_to_customer":
                eventTitle = "Vertragsunterlagen erstellt und an den Kunden übermittelt ( "+plant.contractsSentToCustomerDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
                break;
            case "contract_files_checked":
                eventTitle = "Auftrag erhalten ( "+plant.contractFilesCheckedDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
                break;
            case "cancel_by_customer":
                eventTitle = "Kundenstorno ( "+plant.contractFilesCheckedDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
                break;
            case "delete_plant":
                eventTitle = "PV-Anlage Gelöscht ( "+plant.contractFilesCheckedDate.format(DateTimeFormatter.ofPattern(dateTimeFormat))+" )";
        }

        return eventTitle;
    }
}
