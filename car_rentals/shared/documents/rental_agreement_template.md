# VEHICLE RENTAL AGREEMENT

**Rental Number:** {{rental_number}}
**Date:** {{date}}

---

## PARTIES

**Rental Company:**
{{company_name}}
{{company_address}}
{{company_phone}}
{{company_email}}

**Renter:**
{{renter_name}}
{{renter_address}}
{{renter_phone}}
{{renter_email}}
Driver's License: {{renter_license}}

---

## VEHICLE DETAILS

- **Vehicle:** {{vehicle_make}} {{vehicle_model}} ({{vehicle_year}})
- **License Plate:** {{vehicle_license_plate}}
- **VIN:** {{vehicle_vin}}
- **Color:** {{vehicle_color}}
- **Transmission:** {{vehicle_transmission}}
- **Fuel Type:** {{vehicle_fuel_type}}
- **Mileage at Pickup:** {{pickup_mileage}} km

---

## RENTAL PERIOD

- **Pickup Date:** {{pickup_date}}
- **Pickup Location:** {{pickup_location}}
- **Return Date:** {{return_date}}
- **Return Location:** {{return_location}}
- **Total Days:** {{total_days}}

---

## CHARGES

| Description | Amount |
|------------|--------|
| Daily Rate ({{total_days}} days × {{daily_rate}}) | {{subtotal}} |
| Insurance Fee | {{insurance_fee}} |
| {{#extras}}{{name}} ({{quantity}}) | {{total_price}}{{/extras}} |
| Tax ({{tax_rate}}%) | {{tax_amount}} |
| **Total Amount** | **{{total_amount}}** |
| Security Deposit | {{security_deposit}} |

**Total Due at Pickup:** {{total_amount}}
**Security Deposit (refundable):** {{security_deposit}}

---

## TERMS AND CONDITIONS

1. **Vehicle Use:**
   - The Renter agrees to use the vehicle solely for personal/business transportation
   - The vehicle must not be used for racing, towing, or any illegal activities
   - Smoking in the vehicle is strictly prohibited

2. **Mileage:**
   - Included mileage: {{mileage_limit}} km per day
   - Excess mileage charge: {{excess_mileage_rate}} per km

3. **Fuel Policy:**
   - The vehicle is provided with a full tank
   - The Renter must return the vehicle with a full tank
   - Refueling charge if returned with less fuel: {{refueling_charge}}

4. **Insurance Coverage:**
   - Basic insurance included (deductible: {{insurance_deductible}})
   - {{#insurance_premium}}Premium insurance selected (zero deductible){{/insurance_premium}}
   - Coverage details: {{insurance_details}}

5. **Driver Requirements:**
   - Driver must be at least {{minimum_age}} years old
   - Valid driver's license required
   - {{#additional_drivers}}Additional driver(s): {{additional_driver_names}}{{/additional_drivers}}

6. **Vehicle Return:**
   - Must be returned to {{return_location}} by {{return_date}} at {{return_time}}
   - Late return fee: {{late_return_fee}} per hour
   - Vehicle must be returned in the same condition as received

7. **Damage Responsibility:**
   - The Renter is responsible for any damage to the vehicle
   - All damages will be assessed and charged
   - Security deposit may be used to cover damages

8. **Cancellation Policy:**
   - Free cancellation up to {{cancellation_days}} days before pickup
   - After that, {{cancellation_fee}}% cancellation fee applies

9. **Prohibited Actions:**
   - Subletting or lending the vehicle
   - Taking the vehicle out of {{country}}
   - Driving under the influence of alcohol or drugs
   - Using mobile phone while driving without hands-free device

10. **Roadside Assistance:**
    - 24/7 roadside assistance: {{roadside_phone}}
    - In case of accident: Contact police and rental company immediately

---

## VEHICLE CONDITION AT PICKUP

**Exterior Condition:**
{{pickup_exterior_condition}}

**Interior Condition:**
{{pickup_interior_condition}}

**Fuel Level:** {{pickup_fuel_level}}
**Damage Notes:** {{pickup_damage_notes}}

---

## SIGNATURES

**Renter:**

Signature: ___________________________
Name: {{renter_name}}
Date: {{signature_date}}

**Company Representative:**

Signature: ___________________________
Name: {{manager_name}}
Date: {{signature_date}}

---

**Emergency Contact:** {{emergency_phone}}
**Rental Hotline:** {{rental_hotline}}

---

*This is a legally binding agreement. Please read carefully before signing.*
