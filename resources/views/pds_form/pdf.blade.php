<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Arial Narrow','Arial',sans-serif; font-size: 11px; color: #111; margin: 14px; }
        h1 { font-size: 18px; margin: 4px 0 8px; letter-spacing: 0.3px; }
        .section-title { background: #4f4f4f; color: #fff; padding: 6px 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .table th, .table td { border: 1px solid #6b6b6b; padding: 5px 6px; vertical-align: top; }
        .label { background: #ededed; font-weight: 700; }
        .label.num { width: 22%; }
        .label-small { background: #f3f3f3; font-weight: 600; text-transform: uppercase; font-size: 10px; }
        .muted { color: #666; font-style: italic; }
        .stack { display: block; }
        .text-center { text-align: center; }
        .w-35 { width: 35%; }
        .w-25 { width: 25%; }
        .w-15 { width: 15%; }
        .no-border { border: none !important; }
    </style>
</head>
<body>
    <h1>PERSONAL DATA SHEET</h1>

    {{-- I. Personal Information --}}
    <div class="section-title">I. PERSONAL INFORMATION</div>
    <table class="table">
        <colgroup>
            <col style="width:22%">
            <col style="width:38%">
            <col style="width:18%">
            <col style="width:22%">
        </colgroup>
        <tr>
            <td class="label num">1. SURNAME</td>
            <td colspan="3">{{ $personal->surname ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">2. FIRST NAME</td>
            <td>{{ $personal->firstname ?? '' }}</td>
            <td class="label-small">NAME EXTENSION (JR, SR)</td>
            <td>{{ $personal->name_extension ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">3. MIDDLE NAME</td>
            <td colspan="3">{{ $personal->middlename ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">4. DATE OF BIRTH (dd/mm/yyyy)</td>
            <td>{{ $personal->date_of_birth ?? '' }}</td>
            <td class="label-small">16. CITIZENSHIP</td>
            <td>
                {{ $personal->citizenship ?? '' }}
                @if(!empty($personal->citizenship_details))
                    <div class="muted">{{ $personal->citizenship_details }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label num">5. PLACE OF BIRTH</td>
            <td>{{ $personal->place_of_birth ?? '' }}</td>
            <td class="label-small">SEX</td>
            <td>{{ $personal->sex ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">6. CIVIL STATUS</td>
            <td>{{ $personal->civil_status ?? '' }}</td>
            <td class="label-small">HEIGHT (m)</td>
            <td>{{ $personal->height ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">7. WEIGHT (kg)</td>
            <td>{{ $personal->weight ?? '' }}</td>
            <td class="label-small">BLOOD TYPE</td>
            <td>{{ $personal->blood_type ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">8. GSIS ID NO.</td>
            <td>{{ $personal->gsis_no ?? '' }}</td>
            <td class="label num">9. PAG-IBIG ID NO.</td>
            <td>{{ $personal->pagibig_no ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">10. PHILHEALTH NO.</td>
            <td>{{ $personal->philhealth_no ?? '' }}</td>
            <td class="label num">11. SSS NO.</td>
            <td>{{ $personal->sss_no ?? '' }}</td>
        </tr>
        <tr>
            <td class="label num">12. TIN NO.</td>
            <td>{{ $personal->tin_no ?? '' }}</td>
            <td class="label num">13. AGENCY EMPLOYEE NO.</td>
            <td>{{ $personal->agency_employee_no ?? '' }}</td>
        </tr>
    </table>

    {{-- Address block --}}
    <table class="table">
        <colgroup>
            <col style="width:22%">
            <col style="width:19%">
            <col style="width:19%">
            <col style="width:19%">
            <col style="width:21%">
        </colgroup>
        <tr>
            <td rowspan="2" class="label num">17. RESIDENTIAL ADDRESS</td>
            <td class="label-small">House/Block/Lot No.</td>
            <td class="label-small">Street</td>
            <td class="label-small">Subdivision/Village</td>
            <td class="label-small">Barangay</td>
        </tr>
        <tr>
            <td>{{ $address->present_house_block_lot ?? '' }}</td>
            <td>{{ $address->present_street ?? '' }}</td>
            <td>{{ $address->present_subdivision_village ?? '' }}</td>
            <td>{{ $address->present_barangay ?? '' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label-small">City/Municipality</td>
            <td class="label-small">Province</td>
            <td class="label-small">ZIP Code</td>
            <td class="label-small">Telephone No.</td>
        </tr>
        <tr>
            <td></td>
            <td>{{ $address->present_city_municipality ?? '' }}</td>
            <td>{{ $address->present_province ?? '' }}</td>
            <td>{{ $address->present_zip_code ?? '' }}</td>
            <td>{{ $contact->telephone_no ?? '' }}</td>
        </tr>

        <tr>
            <td rowspan="2" class="label num">18. PERMANENT ADDRESS</td>
            <td class="label-small">House/Block/Lot No.</td>
            <td class="label-small">Street</td>
            <td class="label-small">Subdivision/Village</td>
            <td class="label-small">Barangay</td>
        </tr>
        <tr>
            <td>{{ $address->permanent_house_block_lot ?? '' }}</td>
            <td>{{ $address->permanent_street ?? '' }}</td>
            <td>{{ $address->permanent_subdivision_village ?? '' }}</td>
            <td>{{ $address->permanent_barangay ?? '' }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="label-small">City/Municipality</td>
            <td class="label-small">Province</td>
            <td class="label-small">ZIP Code</td>
            <td class="label-small">Mobile / Email</td>
        </tr>
        <tr>
            <td></td>
            <td>{{ $address->permanent_city_municipality ?? '' }}</td>
            <td>{{ $address->permanent_province ?? '' }}</td>
            <td>{{ $address->permanent_zip_code ?? '' }}</td>
            <td>
                {{ $contact->mobile_no ?? '' }}<br>
                {{ $contact->email_address ?? '' }}
            </td>
        </tr>
    </table>

    {{-- Government ID --}}
    <table class="table">
        <colgroup>
            <col style="width:30%">
            <col style="width:70%">
        </colgroup>
        <tr>
            <td class="label num">19. GOVERNMENT ID (Passport, GSIS, SSS, etc.)</td>
            <td>
                <div><strong>ID Type:</strong> {{ $idInfo->gov_id ?? '' }}</div>
                <div><strong>ID / License / Passport No.:</strong> {{ $idInfo->id_number ?? '' }}</div>
                <div><strong>Date/Place of Issuance:</strong> {{ $idInfo->id_issue_date_place ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- Family background --}}
    <div class="section-title">II. FAMILY BACKGROUND</div>
    <table class="table">
        <thead>
            <tr>
                <th class="label">Type</th>
                <th>Firstname</th>
                <th>Middlename</th>
                <th>Surname</th>
                <th>Extension</th>
                <th>Occupation</th>
                <th>Employer/Business</th>
                <th>Business Address</th>
                <th>Telephone</th>
                <th>Birthdate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($family as $f)
                <tr>
                    <td class="label">{{ strtoupper($f->type) }}</td>
                    <td>{{ $f->firstname }}</td>
                    <td>{{ $f->middlename }}</td>
                    <td>{{ $f->surname }}</td>
                    <td>{{ $f->name_extension }}</td>
                    <td>{{ $f->occupation }}</td>
                    <td>{{ $f->employer }}</td>
                    <td>{{ $f->business_address }}</td>
                    <td>{{ $f->telephone_no }}</td>
                    <td>{{ $f->date_of_birth }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Educational background --}}
    <div class="section-title">III. EDUCATIONAL BACKGROUND</div>
    <table class="table">
        <thead>
            <tr>
                <th class="label">Level</th>
                <th>School Name</th>
                <th>Degree/Course</th>
                <th>From</th>
                <th>To</th>
                <th>Highest Level</th>
                <th>Year Graduated</th>
                <th>Academic Honors</th>
            </tr>
        </thead>
        <tbody>
            @foreach($education as $e)
                <tr>
                    <td class="label">{{ $e->level }}</td>
                    <td>{{ $e->school_name }}</td>
                    <td>{{ $e->degree_course }}</td>
                    <td>{{ $e->from }}</td>
                    <td>{{ $e->to }}</td>
                    <td>{{ $e->highest_level }}</td>
                    <td>{{ $e->year_graduated }}</td>
                    <td>{{ $e->academic_honors }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Civil service --}}
    <div class="section-title">IV. CIVIL SERVICE ELIGIBILITY</div>
    <table class="table">
        <thead>
            <tr>
                <th>Eligibility</th>
                <th>Rating</th>
                <th>Date of Exam/Conferment</th>
                <th>Place of Exam/Conferment</th>
                <th>License No.</th>
                <th>Date of Validity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eligibilities as $el)
                <tr>
                    <td>{{ $el->eligibility }}</td>
                    <td>{{ $el->rating }}</td>
                    <td>{{ $el->exam_date }}</td>
                    <td>{{ $el->exam_place }}</td>
                    <td>{{ $el->license_no }}</td>
                    <td>{{ $el->validity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Work experience --}}
    <div class="section-title">V. WORK EXPERIENCE</div>
    <table class="table">
        <thead>
            <tr>
                <th>Inclusive Dates (From)</th>
                <th>Inclusive Dates (To)</th>
                <th>Position Title</th>
                <th>Department / Agency / Office / Company</th>
                <th>Status of Appointment</th>
                <th>Gov't Service</th>
            </tr>
        </thead>
        <tbody>
            @foreach($work as $w)
                <tr>
                    <td>{{ $w->from }}</td>
                    <td>{{ $w->to }}</td>
                    <td>{{ $w->position_title }}</td>
                    <td>{{ $w->department }}</td>
                    <td>{{ $w->status }}</td>
                    <td>{{ $w->govt_service }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Voluntary work --}}
    <div class="section-title">VI. VOLUNTARY WORK</div>
    <table class="table">
        <thead>
            <tr>
                <th>Name & Address of Organization</th>
                <th>Address</th>
                <th>From</th>
                <th>To</th>
                <th>No. of Hours</th>
                <th>Position / Nature of Work</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voluntary as $v)
                <tr>
                    <td>{{ $v->organization }}</td>
                    <td>{{ $v->address }}</td>
                    <td>{{ $v->from }}</td>
                    <td>{{ $v->to }}</td>
                    <td>{{ $v->hours }}</td>
                    <td>{{ $v->position }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Learning and development --}}
    <div class="section-title">VII. LEARNING AND DEVELOPMENT (L&D)</div>
    <table class="table">
        <thead>
            <tr>
                <th>Title of L&D / Training</th>
                <th>From</th>
                <th>To</th>
                <th>Number of Hours</th>
                <th>Type</th>
                <th>Conducted/Sponsored by</th>
            </tr>
        </thead>
        <tbody>
            @foreach($training as $t)
                <tr>
                    <td>{{ $t->title }}</td>
                    <td>{{ $t->from }}</td>
                    <td>{{ $t->to }}</td>
                    <td>{{ $t->hours }}</td>
                    <td>{{ $t->type_of_ld }}</td>
                    <td>{{ $t->conducted_by }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Other information --}}
    <div class="section-title">VIII. OTHER INFORMATION</div>
    <table class="table">
        <thead>
            <tr>
                <th>Special Skills / Hobbies</th>
                <th>Non-Academic Distinctions / Recognition</th>
                <th>Membership in Association/Organization</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @foreach($otherInfo->where('category','skills') as $o)
                        <div>{{ $o->description }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach($otherInfo->where('category','recognition') as $o)
                        <div>{{ $o->description }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach($otherInfo->where('category','association') as $o)
                        <div>{{ $o->description }}</div>
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>

    {{-- References --}}
    <div class="section-title">IX. REFERENCES</div>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($references as $r)
                <tr>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->address }}</td>
                    <td>{{ $r->contact }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Remarks / Declaration --}}
    <div class="section-title">X. REMARKS / DECLARATION</div>
    <table class="table">
        <tr>
            <td class="label num">Date Accomplished</td>
            <td class="w-25">{{ $declaration->date_accomplished ?? '' }}</td>
            <td class="label num">Remarks</td>
            <td>
                @foreach($remarks as $rm)
                    <div>{{ $rm->remarks }}</div>
                @endforeach
            </td>
        </tr>
    </table>

</body>
</html>
