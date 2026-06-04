<?php include 'views/layouts/header.php'; ?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700;">Doctor Details</h2>
        <p style="color: grey; font-size: 14px;">Doctor / <?= $doctor['name'] ?></p>
    </div>
    <div style="display: flex; gap: 15px;">
        <button class="btn btn-primary" style="background: #00B09B; border: none; display: flex; align-items: center; gap: 10px;">
             <i class="fas fa-plus"></i> Add New Appointment
        </button>
    </div>
</div>

<div class="doctor-grid-layout" style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    
    <!-- Left Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Appointment Schedule (Mock Calendar) -->
        <div class="card p-20" style="background: #FF5B5B; color: white;">
             <h4 style="margin-bottom: 20px;">Appointment Schedule</h4>
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                 <i class="fas fa-chevron-left"></i>
                 <span>July 2025</span>
                 <i class="fas fa-chevron-right"></i>
             </div>
             <!-- Simple Calendar Grid Mock -->
             <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; text-align: center; font-size: 12px;">
                 <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                 <span style="opacity:0.5">29</span><span style="opacity:0.5">30</span><span style="opacity:0.5">1</span><span>2</span><span>3</span><span>4</span><span>5</span>
                 <span>6</span><span>7</span><span>8</span><span>9</span><span>10</span><span>11</span><span>12</span>
                 <span>13</span><span>14</span><span>15</span><span style="background: white; color: #FF5B5B; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; items-center: center;">16</span><span>17</span><span>18</span><span>19</span>
                 <span>20</span><span>21</span><span>22</span><span>23</span><span>24</span><span>25</span><span>26</span>
             </div>
        </div>

        <!-- Doctors Abilities -->
        <div class="card p-20">
             <h4 class="margin-b-20">Doctors Abilities</h4>
             <div style="display: flex; align-items: center; justify-content: center; position: relative; height: 150px;">
                  <!-- Mock Donut Chart -->
                  <div style="width: 100px; height: 100px; border-radius: 50%; border: 15px solid #00B09B; border-right-color: #2D2558; border-bottom-color: #eee;"></div>
                  <div style="position: absolute; font-weight: 700;">85%</div>
             </div>
             <div style="display: flex; justify-content: space-around; margin-top: 20px; font-size: 12px;">
                 <span style="color: #00B09B">Operation</span>
                 <span style="color: #2D2558">Therapy</span>
                 <span style="color: #ccc">Mediation</span>
             </div>
        </div>

    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Profile Card -->
        <div class="card p-20" style="display: flex; gap: 20px;">
             <div style="width: 100px; height: 100px; background: #ddd; border-radius: 12px; position: relative; overflow: hidden;">
                  <?php if(!empty($doctor['profile_image']) && file_exists('assets/images/' . $doctor['profile_image'])): ?>
                        <img src="assets/images/<?= $doctor['profile_image'] ?>?v=1775114101" alt="<?= $doctor['name'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#eee; color:#ccc;">
                             <i class="fas fa-user-md" style="font-size: 40px;"></i>
                        </div>
                  <?php endif; ?>
                  
                  <div style="position: absolute; bottom: -10px; right: -10px; width: 30px; height: 30px; background: #2d2558; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white;">
                        <i class="fas fa-mars"></i> <!-- Gender Icon (Static for now, can be dynamic) -->
                  </div>
             </div>
             <div style="flex: 1;">
                 <div style="display: flex; justify-content: space-between;">
                     <div>
                         <h2 style="margin: 0; font-size: 24px; font-weight: 700;"><?= $doctor['name'] ?></h2>
                         <p style="color: #888; margin: 5px 0 10px;">#D-<?= str_pad($doctor['id'], 5, '0', STR_PAD_LEFT) ?></p>
                         <p style="font-size: 12px; color: #888; margin: 0;"><i class="far fa-clock"></i> Join Date: <?= date('d F Y, h:i A', strtotime($doctor['join_date'])) ?></p>
                     </div>
                     <div style="text-align: right;">
                         <div style="border: 1px solid #ddd; padding: 5px 15px; border-radius: 10px; color: #2d2558; margin-bottom: 10px;">
                             <i class="fas fa-stethoscope"></i> <?= $doctor['specialization'] ?>
                         </div>
                         <div style="color: #ffb800; font-size: 12px;">
                            <?php for($i=0; $i<5; $i++) echo ($i < floor($doctor['rating'])) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                            <span style="color: #888; margin-left: 5px;"><?= $doctor['reviews_count'] ?? 451 ?> reviews</span>
                         </div>
                     </div>
                 </div>
                 
                 <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                 
                 <h4 style="font-size: 14px; margin-bottom: 10px;">Short Biography</h4>
                 <p style="font-size: 12px; line-height: 1.6; color: #666;">
                     <?= $doctor['biography'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.' ?>
                 </p>
                 <a href="#" style="color: var(--primary); font-size: 12px; font-weight: 600;">Read More</a>
             </div>
        </div>

        <!-- Assigned Patient (Graph) -->
        <div class="card p-20">
             <div class="card-header">
                 <h4>Assigned Patient</h4>
                 <div style="color: #00cc66; font-size: 14px; font-weight: 600;">+4%</div>
             </div>
             <div style="display: flex; gap: 20px; align-items: center;">
                 <div style="width: 80px; height: 80px; background: #eee; border-radius: 12px;"></div>
                 <div>
                     <h5 style="margin: 0;">Brian Lucky</h5>
                     <p style="margin: 0; font-size: 12px; color: #888;">Cold & Flu</p>
                     <div style="margin-top: 10px; font-size: 12px;">
                         <a href="#" style="color: #888; margin-right: 15px;">Unassign</a>
                         <a href="#" style="color: #ff4757;">Check Improvement</a>
                     </div>
                 </div>
                 <!-- Mock Graph Line -->
                 <div style="flex: 1; height: 50px; display: flex; align-items: flex-end;">
                     <svg viewBox="0 0 100 20" style="width: 100%; height: 100%;">
                        <path d="M0,15 Q10,5 20,10 T40,15 T60,5 T80,10 T100,5" fill="none" stroke="#00cc66" stroke-width="2" />
                     </svg>
                 </div>
             </div>
        </div>

        <!-- Recent Review -->
        <div class="card p-20">
             <h4 class="margin-b-20">Recent Review</h4>
             
             <?php foreach($reviews as $review): ?>
             <div style="display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #f9f9f9; padding-bottom: 15px;">
                  <div style="width: 50px; height: 50px; background: #ddd; border-radius: 12px;"></div>
                  <div style="flex: 1;">
                      <div style="display: flex; justify-content: space-between;">
                          <h5 style="margin: 0; font-size: 14px;"><?= $review['name'] ?></h5>
                          <span style="font-weight: 700; color: #333;"><?= $review['rating'] ?></span>
                      </div>
                      <p style="margin: 5px 0; font-size: 12px; color: #666; font-style: italic;">"<?= $review['comment'] ?>"</p>
                      <div style="margin-top: 5px;">
                          <?php for($i=0; $i<5; $i++) echo ($i < floor($review['rating'])) ? '<i class="fas fa-star" style="font-size: 10px; color: #ffb800;"></i>' : '<i class="far fa-star" style="font-size: 10px; color: #ccc;"></i>'; ?>
                      </div>
                  </div>
             </div>
             <?php endforeach; ?>
             
             <div style="text-align: center;">
                 <a href="#" style="font-size: 12px; color: #666;">View More</a>
             </div>
        </div>

    </div>

</div>

<?php include 'views/layouts/footer.php'; ?>
