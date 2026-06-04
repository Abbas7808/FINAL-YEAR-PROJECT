<?php include 'views/layouts/header.php'; ?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700;">Patient Details</h2>
        <p style="color: grey; font-size: 14px;">Patient / <?= $patient['name'] ?></p>
    </div>
    <div style="display: flex; gap: 15px;">
         <button class="btn" style="background: white; border: 1px solid #ddd; color: #ff4757;">
            <i class="far fa-times-circle"></i> Reject Patient
        </button>
        <button class="btn btn-primary" style="background: #00cc66; border: none;">
            <i class="far fa-check-circle"></i> Accept Patient
        </button>
    </div>
</div>

<div class="patient-grid-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    
    <!-- Left Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Main Info Card -->
        <div class="card p-20">
            <div style="display: flex; gap: 20px;">
                <div style="width: 100px; height: 100px; background: #e0e0e0; border-radius: 15px; position: relative;">
                    <!-- Gender Icon overlay -->
                    <div style="position: absolute; bottom: -10px; right: -10px; width: 30px; height: 30px; background: #2d2558; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white;">
                        <i class="fas fa-mars"></i>
                    </div>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <h2 style="margin: 0; font-size: 24px; font-weight: 700;"><?= $patient['name'] ?></h2>
                            <p style="color: #888; margin: 5px 0 15px;">#P-<?= str_pad($patient['id'], 5, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        <div style="background: #2d2558; color: white; padding: 10px 20px; border-radius: 12px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-stethoscope"></i>
                            <div>
                                <div style="font-size: 10px; opacity: 0.7;">Disease</div>
                                <div style="font-weight: 600;">Cold & Flu</div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; items-center; gap: 10px; color: #666; font-size: 14px; margin-top: 10px;">
                         <i class="far fa-clock"></i> Check In date: <?= date('d F Y, h:i A', strtotime($patient['created_at'])) ?>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

            <h4 style="margin-bottom: 10px;">Story About Disease</h4>
            <p style="color: #666; line-height: 1.6; font-size: 14px;">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            </p>
            <a href="#" style="color: var(--primary); font-weight: 600; font-size: 14px; display: inline-block; margin-top: 10px;">Read More</a>
        </div>

        <!-- Assigned Doctor -->
        <div class="card p-20">
            <h4 class="margin-b-20">Assigned Doctor</h4>
            <div style="border: 2px dashed #ddd; padding: 30px; text-align: center; border-radius: 12px; color: #888;">
                No Doctor Assigned Yet
            </div>
            <!-- Mock Doctor Selection as per screenshot (Carousel style) -->
            <div style="display: flex; gap: 20px; margin-top: 20px; overflow-x: auto; padding-bottom: 10px;">
                 <div style="min-width: 250px; background: #f9f9fb; padding: 15px; border-radius: 15px; display: flex; gap: 15px; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #ddd; border-radius: 12px;"></div>
                    <div>
                        <h5 style="margin: 0;">Dr. Inggrid A.</h5>
                        <p style="margin: 0; font-size: 12px; color: #888;">Dentist</p>
                    </div>
                 </div>
                 <div style="min-width: 250px; background: #f9f9fb; padding: 15px; border-radius: 15px; display: flex; gap: 15px; align-items: center;">
                    <div style="width: 50px; height: 50px; background: #ddd; border-radius: 12px;"></div>
                    <div>
                        <h5 style="margin: 0;">Dr. Widan Cheeh</h5>
                        <p style="margin: 0; font-size: 12px; color: #888;">Respiratory</p>
                    </div>
                 </div>
            </div>
        </div>
        
        <!-- Disease History -->
        <div class="card p-20" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
             <div>
                <h4 class="margin-b-20">Disease History</h4>
                <div class="timeline">
                    <!-- Timeline items mock -->
                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: column; align-items: center;">
                             <div style="width: 30px; height: 30px; border-radius: 50%; background: #2d2558; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-stethoscope" style="font-size: 12px;"></i></div>
                             <div style="width: 2px; height: 40px; background: #2d2558; margin-top: 5px;"></div>
                        </div>
                        <div>
                             <h5 style="margin: 0;">Diabetes</h5>
                             <p style="margin: 0; font-size: 12px; color: #888;">Sat, 23 Jul 2020, 01:24 PM</p>
                        </div>
                    </div>
                     <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="display: flex; flex-direction: column; align-items: center;">
                             <div style="width: 30px; height: 30px; border-radius: 50%; background: #2d2558; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-stethoscope" style="font-size: 12px;"></i></div>
                             <div style="width: 2px; height: 40px; background: #2d2558; margin-top: 5px;"></div>
                        </div>
                        <div>
                             <h5 style="margin: 0;">Sleep Problem</h5>
                             <p style="margin: 0; font-size: 12px; color: #888;">Sat, 23 Jul 2020, 01:24 PM</p>
                        </div>
                    </div>
                </div>
             </div>

             <div>
                <h4 class="margin-b-20">Patient Statistic</h4>
                <!-- Donut Chart Mock -->
                <div style="display: flex; align-items: center; gap: 20px;">
                     <div style="width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(#00cc66 0% 15%, #ff4757 15% 45%, #2d2558 45% 100%); position: relative;">
                         <div style="position: absolute; width: 80px; height: 80px; background: white; border-radius: 50%; top: 20px; left: 20px;"></div>
                     </div>
                     <div style="flex: 1;">
                         <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 10px;">
                             <span>Immunities (24%)</span>
                             <span>25</span>
                         </div>
                          <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 10px;">
                             <span>Heart Beat (41%)</span>
                             <span>60</span>
                         </div>
                          <div style="display: flex; justify-content: space-between; font-size: 12px;">
                             <span>Weight (15%)</span>
                             <span>7</span>
                         </div>
                     </div>
                </div>
             </div>
        </div>

    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Map/Address Card -->
        <div class="card p-20">
             <div style="height: 150px; background: #eee; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; color: #888;">
                 Map View
             </div>
             
             <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                 <i class="fas fa-map-marker-alt" style="color: var(--primary); margin-top: 3px;"></i>
                 <div>
                     <div style="font-size: 12px; color: #888; margin-bottom: 3px;">Address</div>
                     <div style="font-weight: 600; font-size: 14px;"><?= $patient['address'] ?? '795 Folsom Ave, Suite 600 San Francisco' ?></div>
                 </div>
             </div>
               <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                 <i class="fas fa-phone-alt" style="color: var(--primary); margin-top: 3px;"></i>
                 <div>
                     <div style="font-size: 12px; color: #888; margin-bottom: 3px;">Phone</div>
                     <div style="font-weight: 600; font-size: 14px;"><?= $patient['phone'] ?></div>
                 </div>
             </div>
              <div style="display: flex; gap: 15px;">
                 <i class="fas fa-phone" style="color: var(--primary); margin-top: 3px;"></i>
                 <div>
                     <div style="font-size: 12px; color: #888; margin-bottom: 3px;">Phone</div>
                     <div style="font-weight: 600; font-size: 14px;"><?= $patient['phone'] ?? 'No Phone' ?></div>
                 </div>
             </div>
        </div>

        <!-- Note for Patient -->
        <div class="card p-20" style="background: #2d2558; color: white;">
            <div class="card-header">
                <h4>Note for Patient</h4>
                <i class="fas fa-pen" style="font-size: 12px;"></i>
            </div>
            <p style="font-size: 12px; line-height: 1.6; opacity: 0.8;">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
        </div>

    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>
