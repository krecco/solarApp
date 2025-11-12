package org.bgr.service;

import org.bgr.model.SolarPlantPowerBill;

import javax.enterprise.context.ApplicationScoped;
import javax.validation.Valid;

@ApplicationScoped
public class SolarPlantPowerBillService {
    public void validateUserPowerBill(@Valid SolarPlantPowerBill bill) {}
}