package org.bgr.service;


import io.quarkus.scheduler.Scheduled;
import org.bgr.model.SchedulerModel;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.db.*;

import javax.enterprise.context.ApplicationScoped;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import java.util.List;
import java.util.UUID;

public class SchedulerBackendService {
    //@Scheduled(cron="0 5 * ? * *")
    //@Scheduled(every="100s")

    //every hour for now
    @Scheduled(cron="0 0 5 * * ?") //every day 05:00
    @Transactional
    public void setUserType () {
        System.out.println("setUserType process Start");

        UserBasicInfoModel user = new UserBasicInfoModel();
        EntityManager em = user.getEntityManager();

        UserBasicInfoModel.update("isInvestor = false, isPlantOwner = false");
        UserBasicInfoModel.update("update from UserBasicInfoModel set isPlantOwner = true where id IN (select distinct userId from ProjectUserModel)");
        UserBasicInfoModel.update("update from UserBasicInfoModel set isInvestor = true where id IN (select distinct userId from InvestmentModel)");

        System.out.println("setUserType process End");
    }

    //@Scheduled(every="60s")
    @Scheduled(cron="0 5 5 * * ?") //every day 05:05
    @Transactional
    public void processDashboardData() {
        System.out.println("processDashboardData process Start");

        /*
        public Integer plantForecastSent;
        public Integer plantContractSent;
        public Integer plantContractFinalized;
        public Integer plantInstalled;
        public Integer investmentContractSent;
        public Integer investmentContractFinalized;
        */

        //plantForecastSent
        Long plantForecastSent = SolarPlantModel.count("calculationSentToCustomer = true");
        System.out.println(plantForecastSent);

        //plantContractSent
        Long plantContractSent = SolarPlantModel.count("contractsSentToCustomer = true");
        System.out.println(plantContractSent);

        //plantContractFinalized
        Long plantContractFinalized = SolarPlantModel.count("contractFilesChecked = true");
        System.out.println(plantContractFinalized);

        //plantContractFinalized
        Long plantInstalled = SolarPlantModel.count("contractFilesChecked = true");
        System.out.println(plantInstalled);

        Long plantInUseNr = SolarPlantModel.count("plantInUse = true");
        System.out.println(plantInUseNr);

        /* temp not in use
        //investmentContractSent -- statuses not yet defined!!!
        Long investmentContractSent = InvestmentModel.count("contractFinalized = true");
        System.out.println(investmentContractSent);

        //investmentContractFinalized
        Long investmentContractFinalized = InvestmentModel.count("contractFinalized = true");
        System.out.println(investmentContractFinalized);
         */

        Long userNr = UserBasicInfoModel.count("rs is null OR rs != 99");
        System.out.println(userNr);

        Long webInfoNr = WebInfoModel.count();
        System.out.println(webInfoNr);

        DashboardDataModel data = new DashboardDataModel();
        data.plantForecastSent = plantForecastSent;
        data.plantContractSent = plantContractSent;
        data.plantContractFinalized = plantContractFinalized;
        data.plantInUseNr = plantInUseNr;

        //override until in production and correct statuses
        //data.plantInstalled = plantInstalled;
        //data.investmentContractSent = investmentContractSent;
        //data.investmentContractFinalized = investmentContractFinalized;

        data.plantInstalled = Long.valueOf(0);
        data.investmentContractSent = Long.valueOf(0);
        data.investmentContractFinalized = Long.valueOf(0);


        data.userNr = userNr;
        data.webInfoNr = webInfoNr;

        data.rs = 1;
        data.persist();

        System.out.println("processDashboardData process End");
    }

    @Scheduled(cron="0 10 5 * * ?") //every day 05:10
    @Transactional
    public void processCustomerType() {
        System.out.println("processCustomerType process Start");
        /*
        CustomerType
        0 - nothing yet
        10 - solar plant
        20 - investment
        30 - plant + investment
         */


        List<UserBasicInfoModel> users = UserBasicInfoModel.listAll();
        for (UserBasicInfoModel user : users) {
            Integer customerType = 0;

            if (ProjectUserModel.count("userId", user.id) > 0) {
                customerType += 10;

                System.out.println(user.userId);
                System.out.println(customerType);
            }

            if (InvestmentModel.count("userId", user.id) > 0) {
                customerType += 20;

                System.out.println(user.userId);
                System.out.println(customerType);
            }

            user.customerType = customerType;
            user.persist();
        }

        System.out.println("processCustomerType process Stop");
    }
}
