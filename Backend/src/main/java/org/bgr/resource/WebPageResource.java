package org.bgr.resource;

import biweekly.Biweekly;
import biweekly.ICalendar;
import biweekly.component.VEvent;
import biweekly.parameter.ParticipationLevel;
import biweekly.property.Attendee;
import biweekly.property.Organizer;
import biweekly.util.Duration;
import biweekly.property.Method;

import io.quarkus.hibernate.orm.panache.PanacheQuery;
import io.quarkus.mailer.Mail;
import io.quarkus.mailer.Mailer;
import io.quarkus.panache.common.Sort;
import io.quarkus.qute.Template;
import io.vertx.core.MultiMap;
import io.vertx.core.http.CaseInsensitiveHeaders;
import io.vertx.core.http.HttpServerRequest;
import org.bgr.helper.HtmlMailGenerator;
import org.bgr.helper.ResultCommonObject;
import org.bgr.model.UserAddressModel;
import org.bgr.model.db.*;
import org.bgr.service.ActivityService;
import org.bgr.service.CalendarEventMail;
import org.bgr.service.KeycloakService;
import org.bgr.service.UserService;
import org.eclipse.microprofile.config.inject.ConfigProperty;
import org.keycloak.representations.idm.UserRepresentation;

import javax.annotation.security.RolesAllowed;
import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.MediaType;
import java.io.File;
import java.io.FileOutputStream;
import java.text.SimpleDateFormat;
import java.time.format.DateTimeFormatter;
import java.util.*;

@ApplicationScoped
@Path("web")
public class WebPageResource {
    @ConfigProperty(name = "app.webInfo.notificationMail")
    String webInfoNotificationMail;

    @Inject
    Mailer mailer;

    @Inject
    HtmlMailGenerator htmlMailGenerator;

    @Inject
    KeycloakService keycloakService;

    @Inject
    ActivityService activityService;

    @Context
    HttpServerRequest request;

    @Inject
    Template mail_web_info_intern;

    @Inject
    Template mail_web_info_extern;

    @Inject
    CalendarEventMail calendarEventMail;

    @Inject
    UserService userService;

    @ConfigProperty(name = "app.webInfo.allowFrom")
    String webInfoAllowFrom;

    @ConfigProperty(name = "app.webInfo.notificationMail")
    String notificationMail;

    @POST
    @Path("info")
    @Consumes(MediaType.APPLICATION_JSON)
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject addWebInfo(WebInfoModel webInfo) {
        try {
            String requestHost = request.host().replaceFirst(":\\d+", "");
            if (!webInfoAllowFrom.equals(requestHost)) {
                return new ResultCommonObject(403, "Forbidden");
            }

            webInfo.email = webInfo.email.toLowerCase();

            webInfo.rs = 1;
            webInfo.persist();

            Date now = new Date(); // java.util.Date, NOT java.sql.Date or java.sql.Timestamp!
            String stringDate = new SimpleDateFormat("dd.MM.yyyy', 'HH:mm:ss", Locale.GERMAN).format(now);
            //String stringTermin = webInfo.desiredDateTime.format(DateTimeFormatter.ofPattern("dd.MM.yyyy, HH:mm"));
            String stringTermin = webInfo.desiredDateTime.format(DateTimeFormatter.ofPattern("dd.MM.yyyy"));

            //mail customer -- check to! should be
            String mailTextExtern = mail_web_info_extern.data("stringTermin", stringTermin).render();
            mailer.send(Mail.withHtml(webInfo.email, "Ihre Anfrage an solar.family ist eingegangen", mailTextExtern).addInlineAttachment("logo-solar.png", new File("classes/META-INF/resources/logo-solar.png"), "image/png", "<logo@solar>"));

            //mail solar
            String mailText = mail_web_info_intern.data("webInfo", webInfo).data("stringDate", stringDate).data("stringTermin", stringTermin).render();
            String internMailSubject = "Neue Webanfrage";
            String eventSubject = "Gewünschter Telefontermin für neue Webanfrage";
            String eventDescription = "Am "+stringDate+" ist neue Webanfrage eingegangen.("+webInfo.firstName+" "+webInfo.lastName+", "+webInfo.email+")";
            //String eventDescription = mailText.replaceAll("(<br>)+$", "");

            // disable extra mail
            //mailer.send(Mail.withHtml(webInfoNotificationMail, internSubject, mailText));

           Date desiredDate = java.sql.Timestamp.valueOf(webInfo.desiredDateTime.plusHours(9));
           String sendTo =  notificationMail;


            calendarEventMail.sendEvent(internMailSubject,mailText,eventSubject,eventDescription,desiredDate,sendTo, "");
            return new ResultCommonObject(200, "OK");
        } catch (Exception e) {
            return new ResultCommonObject(400, "Bad Request");
        }

        //to be implemented return new ResultCommonObject(4033, "Forbidden");
    }

    //test ic4j
    @GET
    //@RolesAllowed({"manager", "admin"})
    @Path("info/ic4j")
    @Produces(MediaType.TEXT_PLAIN)
    @Transactional
    public String testIc4j() {
        System.out.println("IC4J Test");

        ICalendar ical = new ICalendar();
        ical.addProperty(new Method(Method.ADD));

        VEvent event = new VEvent();
        event.setSummary("Telefontermin User");
        event.setDescription("This is ics test from Borut Grauf");

        event.setDateStart(new Date());
        event.setDuration(new Duration.Builder()
                .hours(1)
                .build());

        event.setOrganizer(new Organizer("solar.family", "info@solar.family"));

        /*
        Attendee a = new Attendee("Not Amazing Attendee", "not.amazing.attendee@domain");
        a.setParticipationLevel(ParticipationLevel.REQUIRED);
        event.addAttendee(a);
        */

        ical.addEvent(event);
        ical.setProductId("test");

        System.out.println(Biweekly.write(ical).go());

        try{
            FileOutputStream fos = new FileOutputStream("/dev/intranet/solar/documents/generated/ics/mycalendar.ics");

            byte[] strToBytes = Biweekly.write(ical).go().getBytes();
            fos.write(strToBytes);
            fos.close();

            // send normal mail
            /*
            mailer.send(
                Mail.withText("borut.grauf@gmail.com", "Neue Webanfrage - ICS test", "ICS test")
                  .addAttachment("Telefontermin.ics", strToBytes,"text/calendar")

            );
               */


            Map<String, List<String>> headers = new HashMap<>();
            headers.put("Content-Type", Arrays.asList("text/calendar; charset=\"utf-8\"; method=REQUEST"));
            headers.put("Content-Class",  Arrays.asList("urn:content-classes:calendarmessage"));


            //"Content-Type", "text/calendar; charset=UTF-8; method=REQUEST"

            //send event
            /*
            mailer.send(
                Mail.withText("borut.grauf@gmail.com", "Neue Webanfrage - ICS test", "ICS test")
                    .addAttachment("Telefontermin.ics", strToBytes,"text/calendar")
                    .setHeaders(headers)
            );
            */

            System.out.println(Biweekly.write(ical).go().toString());
            String mailContentTest = "<body><img src=3D\"cid:calendar\"></body>";

            /*
            mailer.send(
                    Mail.withHtml("borut.grauf@gmail.com", "Neue Webanfrage - ICS test", mailContentTest)
                            .addAttachment("Telefontermin.ics", strToBytes,"text/calendar")
                            .addInlineAttachment("name", strToBytes, "text/calendar", "calendar")
                            .setHeaders(headers)
            );
            */

            //calendarEventMail.sendEvent();
        } catch (Exception e) {
            System.out.println(e);
        }

        return "OK";
    }

    //!!!!TO BE DELETED ONLY FOR WEB PAGE DEV
    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("info/list-all")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<WebInfoModel> getAll() {
        List<WebInfoModel> res = WebInfoModel.findAll(Sort.by("t0").descending()).list();

        return res;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("info/list/{status}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<WebInfoModel> getByStatus(@PathParam("status") Integer status) {
        List<WebInfoModel> res = WebInfoModel.find("rs", Sort.by("t0").descending(),status).list();

        return res;
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("info/change-status/{id}/{status}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject changeStatus(@PathParam("id") UUID id, @PathParam("status") Integer status) {
        WebInfoModel info = WebInfoModel.findById(id);
        info.rs = status;
        info.persist();

        return new ResultCommonObject(200, "OK");
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("info/create-user/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public ResultCommonObject changeStatus(@PathParam("id") UUID id) {
        WebInfoModel info = WebInfoModel.findById(id);

        UserBasicInfoModel user = UserBasicInfoModel.find("email", info.email).firstResult();
        if (user != null) {
            return new ResultCommonObject(400, "User Exists");
        }

        info.email = info.email.toLowerCase().trim();
        info.firstName = info.firstName.trim();
        info.lastName = info.lastName.trim();

        //create keycloak user
        UserRepresentation keycloakUser = new UserRepresentation();
        keycloakUser.setFirstName(info.firstName);
        keycloakUser.setLastName(info.lastName);
        keycloakUser.setEmail(info.email);
        keycloakUser.setEmailVerified(true);
        keycloakUser.setEnabled(true);
        keycloakUser.setAttributes(Collections.singletonMap("origin", Arrays.asList("backend-added-user")));

        //try to register user in keycloak
        ResultCommonObject res = keycloakService.register(keycloakUser);

        if (res.getStatus() != 200) {
            return new ResultCommonObject(400, "User Exists");
        }

        try {
            //add user role
            keycloakService.addUserToRole(res.getMessage());
        } catch (Exception e) {
            return new ResultCommonObject(400, e.toString());
        }

        UUID newUserId = UUID.fromString(res.getMessage());
        UserBasicInfoModel newUser = new UserBasicInfoModel();

        newUser.userId = newUserId;
        newUser.email = info.email.toLowerCase();
        newUser.firstName = info.firstName;
        newUser.lastName = info.lastName;
        newUser.phoneNr = info.phone;
        newUser.isInvestor = info.isInvestor;
        newUser.gender = info.title;
        newUser.titlePrefix = info.titlePrefix;
        newUser.titleSuffix = info.titleSuffix;

        if (info.isInvestor == false) {
            newUser.isPlantOwner = true;
        }
        newUser.persist();

        //this code will be moved in optimize phase -- we are always in a hurry !!
        //temp for demo

        UserDataStatus userStatus = new UserDataStatus();
        userStatus.userId = newUserId;
        userStatus.persist();

        FileContainerModel fc = new FileContainerModel();
        fc.contextType = 1;
        fc.type = 11;
        fc.contextId = newUserId;
        fc.rs = 1;
        fc.sortOrder = 1;
        fc.uploadOnly = true;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();

        //A_H-solar.family SEPA Lastschriftmandat
        /*
        fc = new FileContainerModel();
        fc.contextType = 1;
        fc.type = 14;
        fc.contextId = newUserId;
        fc.rs = 1;
        fc.sortOrder = 2;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();
         */

        UserAddressModel address = new UserAddressModel();
        address.userId = newUserId;
        address.street = info.addressStreet;
        address.postNr = info.addressPostNr;
        address.city = info.addressCity;
        address.addressExtra = info.addressExtra;
        address.persist();

        UserBasicInfoModel ubm = new UserBasicInfoModel();
        EntityManager em = ubm.getEntityManager();

        //sepa
        String q = new StringBuilder()
                .append("INSERT INTO user_sepa_permission(id, userId, rs) ")
                .append("VALUES ('"+UUID.randomUUID().toString()+"','"+newUserId+"', 1) ")
                .toString();

        em.createNativeQuery(q).executeUpdate();

        ActivityModel activity = new ActivityModel();
        activity.userId = newUser.id;
        activity.contentId = newUser.id;

        activityService.processActivity(activity, "user_register_backend");

        //save to activity instead of notice
        ActivityModel webInfoActivity = new ActivityModel();
        webInfoActivity.userId = newUser.id;
        webInfoActivity.contentId = id;
        activityService.processActivity(webInfoActivity, "web_info");

        info.rs = 2;
        info.persist();


        userService.checkBasicInfoData(newUser.id);
        userService.checkUserAddress(address.id);

        return new ResultCommonObject(200, "OK");
    }

    @GET
    @RolesAllowed({"manager", "admin"})
    @Path("info/latest")
    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public List<WebInfoModel> getLatest() {
        PanacheQuery<WebInfoModel> latest = WebInfoModel.find("rs", Sort.by("t0").descending(),1);
        return latest.range(0, 2).list();
    }
}