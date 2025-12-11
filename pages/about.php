 <!-- Include Header -->
 <?php include_once('../includes/header.php'); ?>


 <?php
    $pageTitle = "Tentang Kami";
    ?>

 <!DOCTYPE html>
 <html lang="id">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title><?php echo $pageTitle; ?> - Sistem Pemesanan Hotel</title> -->

    
     <!-- Custom CSS -->
     <style>
         :root {
             --primary-color: #2c3e50;
             --secondary-color: #3498db;
             --accent-color: #e74c3c;
         }

         /* Hero Section - KEMBALI KE WARNA BIRU YANG BAGUS */
         .hero-about {
             background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.9)), url('../img/bg.jpg');
             background-size: cover;
             background-position: center;
             color: white;
             padding: 100px 0;
             text-align: center;
         }

         .hero-title {
             font-size: 3rem;
             font-weight: 700;
             margin-bottom: 20px;
             text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
         }

         .hero-subtitle {
             font-size: 1.3rem;
             max-width: 800px;
             margin: 0 auto;
             opacity: 0.95;
         }

         /* Team Section */
         .team-section {
             padding: 80px 0;
             background-color: #f8f9fa;
         }

         .section-title {
             position: relative;
             padding-bottom: 15px;
             margin-bottom: 20px;
             font-weight: 700;
             color: var(--primary-color);
             text-align: center;
         }

         .section-title:after {
             content: '';
             position: absolute;
             bottom: 0;
             left: 50%;
             transform: translateX(-50%);
             width: 100px;
             height: 3px;
             background: var(--secondary-color);
             border-radius: 2px;
         }

         .section-subtitle {
             color: #6c757d;
             text-align: center;
             font-size: 1.1rem;
             max-width: 600px;
             margin: 0 auto 50px;
         }

         /* Team Container - 4 CARDS SEBARIS DENGAN GRID */
         .team-container {
             max-width: 1200px;
             margin: 0 auto;
         }

         .team-row {
             display: grid;
             grid-template-columns: repeat(4, 1fr);
             gap: 25px;
             align-items: stretch;
         }

         .team-card {
             background: white;
             border-radius: 12px;
             overflow: hidden;
             transition: all 0.3s ease;
             box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
             border: 1px solid #e9ecef;
             height: 100%;
             display: flex;
             flex-direction: column;
         }

         /* HOVER EFFECT - KEMBALI KE EFEF AWAL YANG BAGUS */
         .team-card:hover {
             transform: translateY(-10px);
             box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
             border-color: var(--secondary-color);
         }

         /* Foto Kotak - PERBAIKI AGAR TIDAK TERPOTONG */
         .photo-container {
             height: 280px;
             width: 100%;
             overflow: hidden;
             position: relative;
             background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
             display: flex;
             align-items: center;
             justify-content: center;
             padding: 15px;
             flex-shrink: 0;
         }

         .member-photo {
             width: auto;
             height: auto;
             max-width: 100%;
             max-height: 100%;
             object-fit: contain;
             transition: all 0.4s ease;
         }

         .team-card:hover .member-photo {
             transform: scale(1.05);
         }

         /* Badge Posisi */
         .position-badge {
             position: absolute;
             bottom: 15px;
             left: 0;
             background: var(--secondary-color);
             color: white;
             padding: 6px 20px;
             font-size: 0.8rem;
             font-weight: 600;
             border-top-right-radius: 15px;
             border-bottom-right-radius: 15px;
             box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
         }

         /* Info Member */
         .member-info {
             padding: 25px 20px;
             text-align: center;
             position: relative;
             flex-grow: 1;
             display: flex;
             flex-direction: column;
             justify-content: space-between;
         }

         .member-name {
             font-size: 1.4rem;
             font-weight: 700;
             color: var(--primary-color);
             margin-bottom: 8px;
             line-height: 1.3;
         }

         .member-position {
             font-size: 0.95rem;
             color: var(--secondary-color);
             font-weight: 600;
             margin-bottom: 20px;
             line-height: 1.4;
         }

         /* Social Icons - WARNA YANG BAGUS */
         .social-icons {
             display: flex;
             justify-content: center;
             gap: 12px;
             margin-top: 15px;
         }

         .social-icon {
             width: 38px;
             height: 38px;
             border-radius: 50%;
             display: flex;
             align-items: center;
             justify-content: center;
             color: white;
             text-decoration: none;
             transition: all 0.3s ease;
             font-size: 16px;
         }

         .social-icon:hover {
             transform: translateY(-3px);
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
         }

         .instagram {
             background: linear-gradient(45deg, #405DE6, #E1306C);
         }

         .linkedin {
             background-color: #0077B5;
         }

         .github {
             background-color: #333;
         }

         /* Contact Section - KEMBALI KE WARNA BIRU */
         .contact-section {
             background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
             color: white;
             padding: 70px 0;
             text-align: center;
         }

         .contact-title {
             font-size: 2.2rem;
             font-weight: 700;
             margin-bottom: 20px;
         }

         .contact-info {
             display: flex;
             justify-content: center;
             gap: 40px;
             margin-top: 30px;
             flex-wrap: wrap;
         }

         .contact-item {
             display: flex;
             align-items: center;
             gap: 12px;
             font-size: 1.1rem;
         }

         .contact-item i {
             background: rgba(255, 255, 255, 0.15);
             width: 45px;
             height: 45px;
             border-radius: 50%;
             display: flex;
             align-items: center;
             justify-content: center;
             font-size: 1.2rem;
         }

         /* Responsive untuk 4 card sebaris */
         @media (max-width: 1200px) {
             .team-row {
                 grid-template-columns: repeat(4, 1fr);
                 gap: 20px;
             }

             .photo-container {
                 height: 260px;
             }
         }

         @media (max-width: 992px) {
             .team-row {
                 grid-template-columns: repeat(2, 1fr);
                 gap: 25px;
             }

             .photo-container {
                 height: 300px;
             }

             .hero-title {
                 font-size: 2.5rem;
             }
         }

         @media (max-width: 768px) {
             .team-row {
                 grid-template-columns: repeat(2, 1fr);
                 gap: 20px;
             }

             .photo-container {
                 height: 250px;
             }

             .hero-title {
                 font-size: 2.2rem;
             }

             .contact-info {
                 flex-direction: column;
                 gap: 20px;
             }

             .member-name {
                 font-size: 1.3rem;
             }
         }

         @media (max-width: 576px) {
             .team-row {
                 grid-template-columns: 1fr;
                 max-width: 320px;
                 margin: 0 auto;
                 gap: 25px;
             }

             .photo-container {
                 height: 280px;
             }
         }

         /* Animation */
         @keyframes fadeIn {
             from {
                 opacity: 0;
                 transform: translateY(20px);
             }

             to {
                 opacity: 1;
                 transform: translateY(0);
             }
         }

         .animate-card {
             opacity: 0;
             animation: fadeIn 0.6s ease forwards;
         }

         .card-1 {
             animation-delay: 0.1s;
         }

         .card-2 {
             animation-delay: 0.2s;
         }

         .card-3 {
             animation-delay: 0.3s;
         }

         .card-4 {
             animation-delay: 0.4s;
         }
     </style>
 <!-- </head>

 <body> -->

     <!-- Team Section -->
     <section class="team-section">
         <div class="container">
             <div class="row">
                 <div class="col-12">
                     <h2 class="section-title">Tim Kami</h2>
                     <p class="section-subtitle">
                         Bertemu dengan orang-orang kreatif yang membuat sistem ini menjadi kenyataan
                     </p>
                 </div>
             </div>

             <!-- 4 CARDS DALAM SATU BARIS DENGAN GRID -->
             <div class="team-container">
                 <div class="team-row">
                     <!-- Member 1: Cristine -->
                     <div class="team-card animate-card card-1">
                         <div class="photo-container">
                             <img src="../img/cristine.png" alt="Cristine Gultom" class="member-photo">
                             <div class="position-badge">PROGRAMMER</div>
                         </div>
                         <div class="member-info">
                             <div>
                                 <h3 class="member-name">Cristine Gultom</h3>
                                 <p class="member-position">Programmer</p>
                             </div>
                             <div class="social-icons">
                                 <a href="https://www.instagram.com/crystyn_e/" target="_blank" class="social-icon instagram" title="Instagram">
                                     <i class="fab fa-instagram"></i>
                                 </a>
                                 <a href="http://www.linkedin.com/in/cristine-hana-tasya-gultom-424855384" target="_blank" class="social-icon linkedin" title="LinkedIn">
                                     <i class="fab fa-linkedin-in"></i>
                                 </a>
                                 <a href="https://github.com/crystyn-e" target="_blank" class="social-icon github" title="GitHub">
                                     <i class="fab fa-github"></i>
                                 </a>
                             </div>
                         </div>
                     </div>

                     <!-- Member 2: Erna -->
                     <div class="team-card animate-card card-2">
                         <div class="photo-container">
                             <img src="../img/erna.png" alt="Ernawati Huta Soit" class="member-photo">
                             <div class="position-badge">PROGRAMMER</div>
                         </div>
                         <div class="member-info">
                             <div>
                                 <h3 class="member-name">Ernawati Huta Soit</h3>
                                 <p class="member-position">Programmer</p>
                             </div>
                             <div class="social-icons">
                                 <a href="https://www.instagram.com/ernaa.wh?igsh=NTJoM3dwcnlkbDd4" target="_blank" class="social-icon instagram" title="Instagram">
                                     <i class="fab fa-instagram"></i>
                                 </a>
                                 <a href="https://www.linkedin.com/in/erna-wati-hutasoit-216718386/" target="_blank" class="social-icon linkedin" title="LinkedIn">
                                     <i class="fab fa-linkedin-in"></i>
                                 </a>
                                 <a href="https://github.com/erwa-soit" target="_blank" class="social-icon github" title="GitHub">
                                     <i class="fab fa-github"></i>
                                 </a>
                             </div>
                         </div>
                     </div>

                     <!-- Member 3: Johannes -->
                     <div class="team-card animate-card card-3">
                         <div class="photo-container">
                             <img src="../img/johanes.png" alt="Johannes Sibarani" class="member-photo">
                             <div class="position-badge">PROGRAMMER</div>
                         </div>
                         <div class="member-info">
                             <div>
                                 <h3 class="member-name">Johannes Sibarani</h3>
                                 <p class="member-position">Programmer</p>
                             </div>
                             <div class="social-icons">
                                 <a href="https://www.instagram.com/jhnssbrn/" target="_blank" class="social-icon instagram" title="Instagram">
                                     <i class="fab fa-instagram"></i>
                                 </a>
                                 <a href="https://www.linkedin.com/in/jmrs-/" target="_blank" class="social-icon linkedin" title="LinkedIn">
                                     <i class="fab fa-linkedin-in"></i>
                                 </a>
                                 <a href="https://github.com/JohannesMRS" target="_blank" class="social-icon github" title="GitHub">
                                     <i class="fab fa-github"></i>
                                 </a>
                             </div>
                         </div>
                     </div>

                     <!-- Member 4: Marthin -->
                     <div class="team-card animate-card card-4">
                         <div class="photo-container">
                             <img src="../img/marthin.png" alt="Marthin Lubis" class="member-photo">
                             <div class="position-badge">PROGRAMMER</div>
                         </div>
                         <div class="member-info">
                             <div>
                                 <h3 class="member-name">Marthin Lubis</h3>
                                 <p class="member-position">Programmer</p>
                             </div>
                             <div class="social-icons">
                                 <a href="https://www.instagram.com/marthinlubis3?igsh=MXJ0OXdiOHJiZWRzaA==" target="_blank" class="social-icon instagram" title="Instagram">
                                     <i class="fab fa-instagram"></i>
                                 </a>
                                 <a href="https://www.linkedin.com/in/marthin-lubis-851b97352" target="_blank" class="social-icon linkedin" title="LinkedIn">
                                     <i class="fab fa-linkedin-in"></i>
                                 </a>
                                 <a href="https://github.com/marthin3" target="_blank" class="social-icon github" title="GitHub">
                                     <i class="fab fa-github"></i>
                                 </a>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </section>

     

     <!-- Include Footer -->
     <?php include_once('../includes/footer.php'); ?>

     <!-- Bootstrap JS Bundle -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

     <!-- Custom JS -->
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             // Handle image errors
             const photos = document.querySelectorAll('.member-photo');
             photos.forEach(photo => {
                 photo.onerror = function() {
                     // Create a colored placeholder with initials
                     const name = this.alt || 'Member';
                     const canvas = document.createElement('canvas');
                     canvas.width = 400;
                     canvas.height = 400;
                     const ctx = canvas.getContext('2d');

                     // Draw background
                     ctx.fillStyle = '#3498db';
                     ctx.fillRect(0, 0, 400, 400);

                     // Draw text
                     ctx.fillStyle = 'white';
                     ctx.font = 'bold 120px Arial';
                     ctx.textAlign = 'center';
                     ctx.textBaseline = 'middle';
                     ctx.fillText(name.charAt(0).toUpperCase(), 200, 200);

                     this.src = canvas.toDataURL();
                 };
             });

             // Smooth scroll for navigation
             document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                 anchor.addEventListener('click', function(e) {
                     e.preventDefault();
                     const targetId = this.getAttribute('href');
                     if (targetId === '#') return;

                     const targetElement = document.querySelector(targetId);
                     if (targetElement) {
                         window.scrollTo({
                             top: targetElement.offsetTop - 80,
                             behavior: 'smooth'
                         });
                     }
                 });
             });

             // Force grid layout untuk 4 kolom
             function maintainFourColumns() {
                 const container = document.querySelector('.team-container');
                 const row = document.querySelector('.team-row');
                 const cards = document.querySelectorAll('.team-card');

                 if (window.innerWidth >= 992) {
                     // Desktop besar: 4 kolom
                     row.style.gridTemplateColumns = 'repeat(4, 1fr)';
                     container.style.maxWidth = '1200px';
                 } else if (window.innerWidth >= 768) {
                     // Tablet: 2 kolom
                     row.style.gridTemplateColumns = 'repeat(2, 1fr)';
                     container.style.maxWidth = '800px';
                 } else {
                     // Mobile: 1 kolom
                     row.style.gridTemplateColumns = '1fr';
                     container.style.maxWidth = '320px';
                 }
             }

             // Panggil saat load dan resize
             window.addEventListener('load', maintainFourColumns);
             window.addEventListener('resize', maintainFourColumns);

             // Pastikan semua card sama tinggi
             function equalizeCardHeights() {
                 const cards = document.querySelectorAll('.team-card');
                 if (cards.length === 0) return;

                 // Reset heights
                 cards.forEach(card => {
                     card.style.height = 'auto';
                 });

                 // Calculate max height
                 let maxHeight = 0;
                 cards.forEach(card => {
                     const height = card.offsetHeight;
                     if (height > maxHeight) {
                         maxHeight = height;
                     }
                 });

                 // Apply max height to all cards
                 cards.forEach(card => {
                     card.style.height = maxHeight + 'px';
                 });
             }

             // Call after images are loaded
             window.addEventListener('load', function() {
                 setTimeout(equalizeCardHeights, 500);
             });

             window.addEventListener('resize', equalizeCardHeights);

             // Add hover effect for cards
             const cards = document.querySelectorAll('.team-card');
             cards.forEach(card => {
                 card.addEventListener('mouseenter', function() {
                     this.style.transition = 'all 0.3s ease';
                 });

                 card.addEventListener('mouseleave', function() {
                     this.style.transition = 'all 0.3s ease';
                 });
             });
         });
     </script>
 </body>

 </html>