
> **Database Note**: This project is using `database/final-schema.sql` as its active live database schema.

# Hospital Management System




### web root 
> http://localhost/Hospitalmanagementsys_test/public/


## Codebase Structure

```
Hospitalmanagementsys_test/
├── app/
│   ├── config/
│   │   └── databaseconnection.php    # PDO database connection configuration
│   └── functions/                    # Helper functions directory
│
├── database/
│   ├── final-schema.sql              # [ACTIVE] Live database schema, triggers, and sample data
│   ├── maintonline.sql               # Database schema backup
│   ├── misc.md                       # Miscellaneous database notes and query references
│   ├── oldschema.sql                 # Previous legacy schema backup
│   ├── procedure.md                  # Documentation and SQL definition for stored procedure
│   ├── querylist.md                  # Comprehensive catalog of all SQL queries, triggers, and procedures
│   ├── schema.sql                    # Initial database structure definition
│   ├── seed.sql                      # Base demo test dataset
│   └── trigger.md                    # Documentation and SQL definitions for database triggers
│
├── public/
│   ├── admin/
│   │   ├── addclient.php             # Admin registration form for creating new Doctor or Patient accounts
│   │   ├── dashboard.php             # Admin overview showing live stats for doctors, patients, admissions, consultations
│   │   ├── deleteclient.php          # Searchable client deletion interface with cascade cleanup
│   │   ├── doctors.php               # Searchable doctor directory with specialization filter
│   │   ├── index.php                 # Admin portal layout, header, and AJAX tab navigation
│   │   └── patients.php              # Searchable registered patients directory
│   │
│   ├── doctor/
│   │   ├── admissionreq.php          # Review, approve, or cancel in-patient hospital admission requests
│   │   ├── consultationreq.php       # Manage pending OPD consultation appointments and record clinical diagnosis
│   │   ├── dashboard.php             # Doctor stats dashboard for pending consultations, admissions, and history counts
│   │   ├── edit.php                  # Update doctor phone, designation, experience, and specialization
│   │   ├── history.php               # Unified history log of all past consultations and hospital treatments with search
│   │   ├── index.php                 # Doctor portal layout, sidebar navigation, and AJAX form handlers
│   │   └── profile.php               # View complete doctor profile details
│   │
│   ├── patient/
│   │   ├── admission.php             # Submit requests for OPD consultations (Planned) or in-patient hospital admissions (Admit)
│   │   ├── dashboard.php             # Patient dashboard showing ongoing pending requests count and history count
│   │   ├── doctorlist.php            # Find and search active doctors by name, designation, and specialization
│   │   ├── edit.php                  # Edit patient demographics (gender, DOB, blood group, address, emergency contact)
│   │   ├── history.php               # View all doctor diagnoses, clinical reports, treatment notes, and status
│   │   ├── index.php                 # Patient portal layout, sidebar navigation, and AJAX form handlers
│   │   └── profile.php               # View patient account and medical profile details
│   │
│   ├── index.php                     # Landing page redirecting visitors to login/portal
│   └── login.php                     # Unified authentication handler for Admin, Doctor, and Patient login & registration
│
├── .gitignore                        # Git ignore configuration
└── Readme.md                         # Project documentation, workflow charts, and system credentials
```


## End-to-End Clinical & Consultation Workflow

```
[ 1. PATIENT REQUEST ]
Location: public/patient/admission.php
Patient selects Doctor, Date & Time, Reason for Visit, and Admission Type:
       │
       ├───► Option A: Type = 'Planned' (OPD Consultation Appointment)
       │     └── DB Table `admission`: status = 'Consult', admission_type = 'Planned'
       │     └── Routed to Doctor Portal ──► "Consultation Requests"
       │
       └───► Option B: Type = 'Admit' (In-Patient Hospital Admission)
             └── DB Table `admission`: status = 'Admitted', admission_type = 'Admit'
             └── Routed to Doctor Portal ──► "Admission Requests"

       │
       ▼

[ 2. DOCTOR CLINICAL ACTION & DECISION ]
Location: public/doctor/

  Scenario A: OPD Consultation (public/doctor/consultationreq.php)
  ├── Doctor reviews patient symptoms and appointment date
  ├── Clicks "Consult / Update"
  ├── Selects Decision Status:
  │     ├── 'Completed' ──► Enters Clinical Diagnosis, Findings & Prescription
  │     └── 'Cancelled' ──► Enters Reason for Cancellation Notes
  └── Saves to DB Table `consultation`:
        └── status = 'Completed' | 'Cancelled'
        └── report = [Clinical Notes / Diagnosis]
        └── consult_datetime = [Timestamp]

  Scenario B: In-Patient Admission (public/doctor/admissionreq.php)
  ├── Doctor reviews patient vitals (Blood Group, Emergency Contact, Symptoms)
  ├── Clicks Action:
  │     ├── 'Approve' ──► Sets DB `admission.status` = 'Admitted', `consultation.status` = 'Approved'
  │     └── 'Cancel'  ──► Sets DB `admission.status` = 'Discharged', `consultation.status` = 'Cancelled'
  └── Saves admission / treatment instructions to DB Table `consultation`

       │
       ▼

[ 3. REAL-TIME MULTI-PORTAL SYNCHRONIZATION ]

  ├── Doctor History (public/doctor/history.php):
  │     └── Unified table displaying all recorded consultations and hospital admissions
  │     └── Includes live search filter and status badges ('Completed', 'Approved', 'Cancelled')
  │
  └── Patient Records (public/patient/history.php):
        └── Patient immediately sees the Doctor's name, diagnosis report, date, and status in real-time
```


## Login execution flow

```
[ index.php ] --(User clicks 'Login/Register')--> [ public/login.php ]
                                                         |
                                        -----------------------------------
                                       |                                   |
                                 [ Login Form ]                    [ Register Form ]
                                       |                                   |
                             Enter Email & Password               Select Role (Doctor/Patient)
                                       |                          Enter Name, Email, Password
                                       |                                   |
                                        -----------------------------------
                                                         |
                                          (Form POSTs data to login.php)
                                                         |
                                              [ PHP Backend Processing ]
                                                         |
                            -----------------------------------------------------------
                           |                                                           |
                 [ If Action == Login ]                                     [ If Action == Register ]
                           |                                                           |
               Fetch user by email from DB                                 Hash the password securely
                           |                                                           |
               Verify password using hash                                  Insert new user into Database
                           |                                                           |
                 Check User's Role                                              Show Success Message
                           |                                                   (User can now log in)
        ---------------------------------------
        |                  |                  |
   Role = Admin       Role = Doctor      Role = Patient
        |                  |                  |
   Redirect to        Redirect to        Redirect to
 public/admin/       public/doctor/     public/patient/

```


# login info 



```
admin : email -newadmin@gmail.com
        pass - newadmin

doctor : email - drhasanpiker@gmail.com
         pass -  drhasanpiker 

patient : email - abc123@gmail.com
         pass -  abc123   
```

> database name in my xampp is > hms_opd_new , user > root , pass > empty


<br>
<br>
