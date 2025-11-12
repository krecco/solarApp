package org.bgr.service;

import org.bgr.helper.ResultCommonObject;
import org.bgr.model.SolarPlantModel;
import org.bgr.model.SolarPlantPowerBill;
import org.bgr.model.db.FileContainerModel;
import org.bgr.model.db.ProjectUserModel;
import org.bgr.model.db.UserBasicInfoModel;

import javax.enterprise.context.ApplicationScoped;
import javax.persistence.EntityManager;
import javax.transaction.Transactional;
import java.time.LocalDateTime;
import java.util.UUID;

@ApplicationScoped
public class SolarPlantCloneService {

    @Transactional
    public ResultCommonObject clonePlant(UUID plantId) {
        SolarPlantModel plant = SolarPlantModel.findById(plantId);

        //detach entity
        EntityManager em = plant.getEntityManager();
        em.detach(plant);

        plant.id = null;
        plant.planDocumentUploaded = false;
        plant.calculationInProgress = false;
        plant.calculationFinished = false;
        plant.calculationChecked=false;
        plant.calculationSentToCustomer=false;
        plant.calculationSentToCustomerDate=null;
        plant.calculationMailWithRegistration=false;
        plant.orderInterest=false;
        plant.orderInterestDate=null;
        plant.orderInterestAccepted=false;
        plant.orderInterestAcceptedDate=null;
        plant.contractSignedAndUploaded=false;
        plant.contractSignedAndUploadedDate=null;
        plant.contractsReviewed=false;
        plant.contractsSentToCustomer=false;
        plant.contractsSentToCustomerDate=null;
        plant.contractsSentToCustomerBackendUserSendTo="";
        plant.contractsSigned=false;
        plant.contractFilesChecked=false;
        plant.contractFilesCheckedDate=null;
        plant.propertyOwnerListFinished=false;
        plant.t0 = null;

        plant.title = "(Variante 2) "+plant.title;

        plant.cloneSource = plantId;
        System.out.println(plant.id);

        try{
            plant.persist();
            insertProject(plantId, plant.id);
            clonePowerBill(plantId, plant.id);
            insertFileContainers(plant.id);

            return new ResultCommonObject(200, plant.id.toString());
        } catch (Exception e) {
            System.out.println(e.toString());
            return new ResultCommonObject(500, "Server Error");
        }
    }

    @Transactional
    private void insertProject(UUID sourcePlantId, UUID targetPlantId) {
        ProjectUserModel pum = ProjectUserModel.find("plantId", sourcePlantId).firstResult();

        ProjectUserModel pu = new ProjectUserModel();
        pu.plantId = targetPlantId;
        pu.userId = pum.userId;
        pu.persist();
    }

    @Transactional
    private void clonePowerBill(UUID sourcePlantId, UUID targetPlantId) {
        SolarPlantPowerBill bill = SolarPlantPowerBill.find("plantId", sourcePlantId).firstResult();

        //detach entity
        EntityManager em = bill.getEntityManager();
        em.detach(bill);

        bill.id = null;
        bill.plantId = targetPlantId;

        bill.persist();
    }

    @Transactional
    private void insertFileContainers(UUID targetPlantId) {
        FileContainerModel fc = new FileContainerModel();
        fc.contextType = 2;
        fc.type = 21;
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
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
        fc.contextId = targetPlantId;
        fc.rs = 1;
        fc.sortOrder = 8;
        fc.uploadOnly = false;
        fc.downloadOnly = false;
        fc.backendUploadOnly = false;
        fc.backendOnly = false;
        fc.noStatusUpdate = false;
        fc.persist();
    }
}
