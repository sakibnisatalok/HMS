
# Hospital Management System




### web root 
> http://localhost/Hospitalmanagementsys_test/public/


## Codebase Structure

```
Hospitalmanagementsys_test/
├── app/
│   ├── config/
│   │   └── databaseconnection.php
│   └── functions/
│
├── database/
│   ├── misc.md
│   ├── schema.sql
│   └── seed.sql
│
├── public/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── doctors.php
│   │   ├── index.php
│   │   └── patients.php
│   ├── doctor/
│   │   ├── admissionreq.php
│   │   ├── consultationreq.php
│   │   ├── dashboard.php
│   │   ├── edit.php
│   │   ├── history.php
│   │   ├── index.php
│   │   └── profile.php
│   ├── patient/
│   │   ├── admission.php
│   │   ├── dashboard.php
│   │   ├── doctorlist.php
│   │   ├── edit.php
│   │   ├── history.php
│   │   ├── index.php
│   │   └── profile.php
│   ├── index.php
│   └── login.php
├── .gitignore
└── Readme.md
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

> database name in xampp is > hms_opd_new , user > root , pass > empty


<br>
<br>
