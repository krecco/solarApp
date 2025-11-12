package org.bgr.service;

import io.quarkus.panache.common.Sort;
import io.quarkus.scheduler.Scheduled;
import org.bgr.model.SchedulerModel;
import org.bgr.model.db.UserBasicInfoModel;

import javax.inject.Inject;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import java.time.LocalDateTime;
import java.util.List;
import java.util.UUID;

public class SchedulerService {
    //@Scheduled(cron="0 5 * ? * *")
    //@Scheduled(every="100s")
    @Inject
    MailService mailService;


    @Scheduled(every="300s")
    @Transactional
    void schedulerActivityMailNotification () {
        System.out.println("--- schedulerActivityMailNotification start --- ");

        LocalDateTime dateFrom = LocalDateTime.now().minusMinutes(5);

        //get userIds
        String query = new StringBuilder()
                .append("SELECT DISTINCT(cast(contextId as varchar)) ")
                .append("FROM  scheduler ")
                .append(" WHERE rs = 0 ")
                .append(" AND function = 'activityMailNotification' ")
                .append(" AND t0 <= '"+dateFrom+"'")
                .append(" AND contextId NOT IN (SELECT contextId FROM scheduler WHERE t0 > '"+dateFrom+"')")
                .toString();

        SchedulerModel schedulerModel = new SchedulerModel();
        EntityManager em = schedulerModel.getEntityManager();
        List<String> results = em.createNativeQuery(query).getResultList();

        System.out.println("-----------------------");
        System.out.println(query);
        System.out.println(results);
        System.out.println(dateFrom);
        System.out.println("-----------------------");

        if (!results.isEmpty()) {
            for (String result : results) {
                System.out.println(result);
                try {
                    UUID userID = UUID.fromString(result);
                    System.out.println(userID);

                    List<SchedulerModel> scheduleList = SchedulerModel.find("contextId=?1 AND function=?2 AND rs=0", Sort.by("t0").descending(), userID, "activityMailNotification").list();
                    UserBasicInfoModel user = UserBasicInfoModel.findById(userID);
                    System.out.println(user.email);

                    //Disable user notifications
                    //mailService.sendScheduledMailNotification(user.email, scheduleList);

                    //todo check if sent!
                    for (SchedulerModel schedule : scheduleList) {
                        schedule.rs = 1;
                        schedule.persist();
                    }

                } catch (Exception e) {
                    System.out.println(e);
                    if (result == null) {
                        SchedulerModel.update("update from SchedulerModel set rs=10 where contextId is null");
                    }
                }

            }
        }
        System.out.println("--- schedulerActivityMailNotification end --- ");
    }
}
