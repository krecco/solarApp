# VEHICLE INSPECTION CHECKLIST

**Rental Number:** {{rental_number}}
**Vehicle:** {{vehicle_make}} {{vehicle_model}} ({{vehicle_year}})
**License Plate:** {{vehicle_license_plate}}
**Date:** {{inspection_date}}
**Time:** {{inspection_time}}
**Inspector:** {{inspector_name}}

---

## INSPECTION TYPE

- [ ] Pickup Inspection
- [ ] Return Inspection

---

## EXTERIOR INSPECTION

### Body Condition
- [ ] Front Bumper: {{front_bumper_condition}}
- [ ] Rear Bumper: {{rear_bumper_condition}}
- [ ] Hood: {{hood_condition}}
- [ ] Roof: {{roof_condition}}
- [ ] Left Side: {{left_side_condition}}
- [ ] Right Side: {{right_side_condition}}
- [ ] Trunk: {{trunk_condition}}

### Lights & Signals
- [ ] Headlights (Low/High): Working / Not Working
- [ ] Tail Lights: Working / Not Working
- [ ] Turn Signals: Working / Not Working
- [ ] Brake Lights: Working / Not Working
- [ ] Hazard Lights: Working / Not Working

### Tires & Wheels
- [ ] Front Left Tire: {{fl_tire_condition}} | Pressure: {{fl_tire_pressure}}
- [ ] Front Right Tire: {{fr_tire_condition}} | Pressure: {{fr_tire_pressure}}
- [ ] Rear Left Tire: {{rl_tire_condition}} | Pressure: {{rl_tire_pressure}}
- [ ] Rear Right Tire: {{rr_tire_condition}} | Pressure: {{rr_tire_pressure}}
- [ ] Spare Tire: Present / Missing

### Windows & Mirrors
- [ ] Windshield: {{windshield_condition}}
- [ ] Rear Window: {{rear_window_condition}}
- [ ] Left Mirror: {{left_mirror_condition}}
- [ ] Right Mirror: {{right_mirror_condition}}
- [ ] Side Windows: {{side_windows_condition}}

---

## INTERIOR INSPECTION

### Dashboard & Controls
- [ ] Steering Wheel: {{steering_condition}}
- [ ] Dashboard: {{dashboard_condition}}
- [ ] Instrument Cluster: Working / Not Working
- [ ] Air Conditioning: Working / Not Working
- [ ] Heating: Working / Not Working
- [ ] Radio/Entertainment System: Working / Not Working

### Seats & Upholstery
- [ ] Front Seats: {{front_seats_condition}}
- [ ] Rear Seats: {{rear_seats_condition}}
- [ ] Seat Belts: All Working / Issues
- [ ] Floor Mats: Present / Missing

### Cleanliness
- [ ] Interior Cleanliness: Clean / Needs Cleaning / Dirty
- [ ] Odor: None / Minor / Strong
- [ ] Trash: None / Minimal / Excessive

---

## MECHANICAL CHECKS

- [ ] Engine: {{engine_condition}}
- [ ] Transmission: {{transmission_condition}}
- [ ] Brakes: {{brakes_condition}}
- [ ] Suspension: {{suspension_condition}}
- [ ] Exhaust: {{exhaust_condition}}

---

## FLUIDS & LEVELS

- [ ] Engine Oil: {{oil_level}}
- [ ] Coolant: {{coolant_level}}
- [ ] Brake Fluid: {{brake_fluid_level}}
- [ ] Windshield Washer Fluid: {{washer_fluid_level}}
- [ ] Fuel Level: {{fuel_level}}

---

## DOCUMENTS & ACCESSORIES

- [ ] Vehicle Registration: Present / Missing
- [ ] Insurance Documents: Present / Missing
- [ ] Owner's Manual: Present / Missing
- [ ] First Aid Kit: Present / Missing
- [ ] Warning Triangle: Present / Missing
- [ ] Fire Extinguisher: Present / Missing
- [ ] Jack & Tools: Present / Missing
- [ ] Keys (quantity): {{key_quantity}}

---

## EXTRAS INSTALLED

{{#extras}}
- [ ] {{extra_name}}: Installed / Removed
{{/extras}}

---

## MILEAGE

**Current Mileage:** {{current_mileage}} km

---

## DAMAGE REPORT

### Existing Damage (Before Rental)
{{#existing_damage}}
- Location: {{damage_location}} | Type: {{damage_type}} | Severity: {{damage_severity}}
{{/existing_damage}}

### New Damage (After Rental)
{{#new_damage}}
- Location: {{damage_location}} | Type: {{damage_type}} | Severity: {{damage_severity}} | Estimated Cost: {{damage_cost}}
{{/new_damage}}

---

## PHOTOS

{{#photos}}
- {{photo_description}}: {{photo_url}}
{{/photos}}

---

## ADDITIONAL NOTES

{{additional_notes}}

---

## SIGNATURES

**Customer:**

I acknowledge that the above inspection is accurate and I accept the vehicle in its current condition.

Signature: ___________________________
Name: {{customer_name}}
Date: {{signature_date}}

**Inspector:**

Signature: ___________________________
Name: {{inspector_name}}
Position: {{inspector_position}}
Date: {{signature_date}}

---

**Inspection completed at:** {{completion_time}}
