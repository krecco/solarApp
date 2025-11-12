package org.bgr.service;

import io.quarkus.qute.Template;
import org.bgr.helper.FileHelper;
import org.bgr.helper.HtmlDocumentGenerator;
import org.bgr.helper.HtmlToPdfConverter;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.SolarPlantPowerBill;

import javax.enterprise.context.ApplicationScoped;
import javax.inject.Inject;
import javax.transaction.Transactional;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import java.util.UUID;
import org.eclipse.microprofile.config.inject.ConfigProperty;

@ApplicationScoped
public class SolarPowerService {

    public class GenerateFileResponse {
        public String fileName;
    }

    @ConfigProperty(name = "app.folder.documents")
    String documentFolder;

    @Inject
    Template forecast_calculation;

    /* Leve as reference when file location not in resource folder
    @Location("forecast_calculation.html")
    Template forecast_calculation;
    */

    @Inject
    HtmlDocumentGenerator htmlDocumentGenerator;

    @Inject
    FileHelper fileHelper;

    @Produces(MediaType.APPLICATION_JSON)
    @Transactional
    public GenerateFileResponse powerForecastCalculation1(UUID plantId, UUID billId) {
        String folder = documentFolder+"generated/forecast_calculation/";

        SolarPlantModel plant = SolarPlantModel.findById(plantId);
        SolarPlantPowerBill powerBill = SolarPlantPowerBill.findById(billId);

        //todo what is BOIb
        Double BOIb = 0.209832298;

        Double surPlusPower = plant.powerProductionForecast - plant.powerConsumptionForecast;

        //todo where does 0.0753 comes from
        Double creditPrediction = surPlusPower * 0.065; //(BOZ5)
        //

        //todo save 250 to db in config
        Double kpc = plant.nominalPower * 250;


        //todo unit price is to be caLCULATED
        Double finalPrice = plant.unitPrice - kpc - plant.planCost;

        //Double finalPrice = 0.0;

        Double powerConsumption = powerBill.consumption - plant.powerConsumptionForecast;
        Double powerExpenditure = powerConsumption * BOIb;

        Double PVcostToSolar = plant.powerConsumptionForecast * plant.consumptionValuePerKW;
        Double PVsurPlusSolar = surPlusPower * plant.productionValuePerKW;

        Double electricityExpenditureTotal = powerExpenditure - creditPrediction + PVcostToSolar + PVsurPlusSolar;
        Double duration = finalPrice / (PVcostToSolar+PVsurPlusSolar);
        if (duration > 12.5) {
            duration = 12.5;
        }

        Double openBalance = finalPrice - duration * (PVcostToSolar+PVsurPlusSolar);

        String forecastHtmlAsString = forecast_calculation
                .data("forecastTitle", "Sonnenkraftwerk Ollersdorf IV Prognoserechnung*")
                .data("powerBill", powerBill)
                .data("plant", plant)
                .data("surPlusPower", surPlusPower)
                .data("kpc", kpc)
                .data("finalPrice", finalPrice)
                .data("powerConsumption", powerConsumption)
                .data("powerExpenditure", powerExpenditure)
                .data("creditPrediction", creditPrediction)
                .data("PVcostToSolar", PVcostToSolar)
                .data("PVsurPlusSolar", PVsurPlusSolar)
                .data("electricityExpenditureTotal", electricityExpenditureTotal)
                .data("duration", duration)
                .data("openBalance", openBalance)
                .render();

        String htmlString =  htmlDocumentGenerator.generateHtmlDoc(forecastHtmlAsString);

        try {
            if (fileHelper.saveToDisk(folder,billId.toString() +"-"+ plantId.toString() +".html", htmlString) == true) {
                System.out.println("Html file created");

                //don't use injection as it will be an external function
                HtmlToPdfConverter convertToPdf = new HtmlToPdfConverter();
                Boolean status = convertToPdf.convertHtmlFileToPdf("file:///"+folder + "/" + billId.toString() +"-"+ plantId.toString() +".html", folder+"/"+ billId.toString() +"-"+ plantId.toString() +".pdf");

                //do delete
                //Boolean tmp = convertToPdf.convertHtmlFileToPdf("file:///"+folder + "/" + billId.toString() +"-"+ plantId.toString() +".html", "/home/cylon/Development/intranet/solar/backend/src/main/resources/META-INF/resources/generated_files/"+ billId.toString() +"-"+ plantId.toString() +".pdf");

                System.out.println("PDF create Status: "+status);
            }
        } catch (Exception e) {
            System.out.println(e);
        } finally {

            GenerateFileResponse res = new GenerateFileResponse();
            res.fileName = billId.toString() +"-"+ plantId.toString() +".pdf";

            return res;
        }
    }
}
