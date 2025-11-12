package org.bgr.service;

import com.fasterxml.jackson.databind.node.ObjectNode;
import org.bgr.helper.NativeQueryToJson;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.db.InvestmentModel;

import javax.enterprise.context.ApplicationScoped;
import javax.persistence.EntityManager;
import javax.persistence.Tuple;
import javax.validation.Valid;
import javax.ws.rs.QueryParam;
import java.util.List;
import java.util.UUID;

@ApplicationScoped
public class SolarPlantRepaymentService {

    public ObjectNode getStatsForPeriod(Integer calculationYear, Integer calculationYearPeriod) {

        String filterStatus = " WHERE sp.contractFilesChecked is true AND sp.plantInUse is true ";

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
                //.append("SELECT SUM(sp.unitPrice) as sumUnitPrice, COALESCE(SUM(sprl.amount),0) as sumRepaid,  COALESCE(SUM(sprlu.amount),0) as sumOpen ")
                .append("SELECT COALESCE(SUM(sprl.amount),0) as sumRepaid,  COALESCE(SUM(sprlu.amount),0) as sumOpen ")
                .append("FROM solar_plant sp ")
                .append("LEFT JOIN solar_plant_repayment_log sprl on (sprl.plantid = sp.id AND sprl.paymentverified IS TRUE) ")
                .append("LEFT JOIN solar_plant_repayment_log sprlu on (sprlu.plantid = sp.id AND sprlu.paymentverified IS NOT TRUE) ")
                .append(filterStatus);

        SolarPlantModel spm = new SolarPlantModel();
        EntityManager em = spm.getEntityManager();

        //System.out.println(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());
        NativeQueryToJson parser = new NativeQueryToJson();
        List<ObjectNode> res = parser.toJsonArray(em.createNativeQuery(plantQuery.toString(), Tuple.class).getResultList());

        return res.get(0);
    }

    public List<String> getPlantRepaymentsByPeriod(UUID plantId, Integer calculationYear, Integer calculationYearPeriod) {
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

        StringBuilder query = new StringBuilder()
                .append("SELECT cast(id as varchar) as id ")
                .append("FROM solar_plant_repayment_log ")
                .append("WHERE plantid = '"+plantId+"' ")
                .append("AND paymentverified is not true ");

        if (!filterRepaymentPeriod.equals("%/%")) {
            query.append("AND repaymentperiod LIKE '"+filterRepaymentPeriod+"' ");
        }

        SolarPlantModel spm = new SolarPlantModel();
        EntityManager em = spm.getEntityManager();
        return em.createNativeQuery(query.toString()).getResultList();
    }
}