<?php 
session_start();
session_regenerate_id(true);
include 'db_connect.php';

// Siguraduhin na naka-login at teacher role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture, cover_photo, bio, role FROM teachers WHERE id = ?");
$stmt->execute([$user_id]);
$teacher = $stmt->fetch();

// Fallback values
$profile_picture = !empty($teacher['profile_picture']) ? $teacher['profile_picture'] : 'PROFILE.webp';
$cover_photo = !empty($teacher['cover_photo']) ? $teacher['cover_photo'] : 'cover.jpg';
$bio = !empty($teacher['bio']) ? $teacher['bio'] : 'Look always seems impossible until its done | Lifelong Learner ✨';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teachers Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  <style>
    /* Custom Styles */
.btn-primary {
  background: linear-gradient(45deg, #6a11cb, #2575fc);
  border: none;
  color: white;
  padding: 10px 20px;
  border-radius: 30px;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(45deg, #2575fc, #6a11cb);
  box-shadow: 0 4px 15px rgba(38, 100, 255, 0.3);
}

.shadow-card {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.modal-content {
  animation: fadeIn 0.5s ease-out;
}

/* Modal Fade-in Effect */
@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

/* Profile Image Styles */
.profile-img {
  width: 128px;
  height: 128px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* Floating particles effect */
@keyframes floatingParticles {
  0% {
    transform: translateY(0);
    opacity: 0.6;
  }
  50% {
    transform: translateY(-30px);
    opacity: 1;
  }
  100% {
    transform: translateY(0);
    opacity: 0.6;
  }
}

.particle {
  position: absolute;
  width: 6px;
  height: 6px;
  background: rgba(255, 255, 255, 0.4);
  border-radius: 50%;
  animation: floatingParticles 4s infinite ease-in-out;
}

/* Back button */
.back-btn {
  position: absolute;
  top: 10px; /* Inilipat pataas */
  left: 10px; /* Nilagay sa kaliwang gilid */
  transform: none; /* Inalis ang centering */
  color: white;
  text-decoration: none;
  font-size: 18px;
  background: rgba(255, 255, 255, 0.2);
  padding: 8px 12px;
  border-radius: 8px;
  transition: background 0.3s ease;
}

/* Responsive Styles */
@media (max-width: 768px) {
  /* Adjust profile image size on smaller screens */
  .profile-img {
    width: 100px;
    height: 100px;
  }

  /* Make profile card elements more stacked */
  .back-btn {
    font-size: 16px;
    padding: 6px 10px;
  }

  .btn-primary {
    padding: 8px 15px;
    font-size: 14px;
  }

  .container {
    padding: 10px;
  }

  /* Adjust card layout for mobile */
  .max-w-3xl {
    width: 100%;
    padding: 10px;
  }

  /* Profile header and name styling */
  .text-3xl {
    font-size: 24px;
  }

  /* Modify cover photo image size */
  #coverPhotoDisplay {
    height: 250px;
  }

  /* Add spacing adjustments to modal */
  .modal-content {
    margin-top: 20px;
    padding: 15px;
  }

  /* Adjust spacing for profile content */
  .ml-40 {
    margin-left: 0;
    text-align: center;
  }
}

@media (max-width: 480px) {
  /* Further adjustments for very small screens */
  .text-3xl {
    font-size: 20px;
  }

  .profile-img {
    width: 80px;
    height: 80px;
  }

  .btn-primary {
    padding: 6px 12px;
    font-size: 12px;
  }

  .back-btn {
    font-size: 14px;
    padding: 5px 8px;
  }

  /* Decrease spacing and adjust button layouts */
  .container {
    padding: 8px;
  }

  /* Profile name and email adjustments for mobile */
  .text-lg {
    font-size: 14px;
  }
}


  </style>
</head>
<body class="bg-gray-800 text-white">

<a href="javascript:history.back()" class="back-btn">← Back</a>

<div class="container mx-auto p-6">
  <!-- Profile Card -->
  <div class="max-w-3xl mx-auto bg-gray-900 shadow-card rounded-xl overflow-hidden">
    <!-- Cover Photo -->
    <div class="relative">
      <img id="coverPhotoDisplay" src="<?php echo $cover_photo; ?>" alt="Cover Photo" class="w-full h-64 object-cover rounded-xl">
      <button id="editCoverBtn" class="absolute top-3 right-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white px-4 py-1 text-xs rounded-md shadow-lg hover:opacity-80 transition">Change Cover</button>
    </div>
    
    <!-- Profile Info -->
    <div class="p-6 relative">
      <div class="absolute -top-16 left-6">
        <img id="profilePicDisplay" src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-img">
        <button id="editProfilePhotoBtn" class="mt-3 btn-primary px-6 py-1 text-xs">Change Photo</button>
      </div>
      
      <div class="ml-40">
        <!-- Name: Bright white for prominence -->
  <h2 class="text-3xl font-semibold mb-2 text-white"><?php echo htmlspecialchars($teacher['first_name'] . " " . $teacher['last_name']); ?></h2>

<!-- Email: Softer white/gray for readability -->
<p class="text-lg text-gray-300"><?php echo htmlspecialchars($teacher['email']); ?></p>

<!-- Bio: Softer white for elegance -->
<p class="mt-4 text-lg text-gray-300"><?php echo htmlspecialchars($bio); ?></p>

<!-- Role: Soft blue to differentiate -->
<p class="mt-4 font-semibold text-blue-400">Role: <?php echo ucfirst(htmlspecialchars($teacher['role'])); ?></p>

        <button id="editProfileBtn" class="mt-3 btn-primary px-6 py-2 text-xs">Edit Profile</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center hidden z-50">
  <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-96 relative modal-content">
    <button id="closeEditProfile" class="absolute top-2 right-2 bg-gray-500 text-white rounded-full p-2">✖</button>
    <h3 class="text-2xl font-semibold mb-4 text-center">Edit Profile</h3>
    <form id="editProfileForm" class="space-y-4">
      <input type="text" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>" placeholder="First Name" class="w-full p-2 bg-gray-700 rounded-lg text-white" required>
      <input type="text" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>" placeholder="Last Name" class="w-full p-2 bg-gray-700 rounded-lg text-white" required>
      <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" placeholder="Email" class="w-full p-2 bg-gray-700 rounded-lg text-white" required>
      <textarea name="bio" placeholder="Bio" class="w-full p-2 bg-gray-700 rounded-lg text-white h-20 resize-none" maxlength="150"><?php echo htmlspecialchars($bio); ?></textarea>
      <!-- Role display; not editable -->
      <input type="text" name="role" value="<?php echo ucfirst(htmlspecialchars($teacher['role'])); ?>" class="w-full p-2 bg-gray-700 rounded-lg text-white" disabled>
      <button type="submit" class="btn-primary w-full py-2">Save Changes</button>
    </form>
  </div>
</div>

<!-- Change Profile Photo Modal -->
<div id="editProfilePhotoModal" class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center hidden z-50">
  <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-96 relative modal-content">
    <button id="closeProfilePhotoModal" class="absolute top-2 right-2 bg-gray-500 text-white rounded-full p-2">✖</button>
    <h3 class="text-2xl font-semibold mb-4 text-center">Change Profile Photo</h3>
    <div class="flex flex-col items-center">
      <img id="profilePhotoPreview" src="<?php echo $profile_picture; ?>" alt="Profile Preview" class="w-32 h-32 rounded-full border-4 border-gray-300 mb-4">
      <input type="file" id="profilePhotoInput" accept="image/*" class="mb-4 p-2 bg-gray-700 text-white rounded">
      <div class="flex space-x-4">
      <button id="uploadProfilePhotoBtn" class="btn-primary">Done</button>
<button id="removeProfilePhotoBtn" class="btn-primary remove-btn">Remove</button>

      </div>
    </div>
  </div>
</div>

<!-- Change Cover Photo Modal -->
<div id="editCoverPhotoModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
<div class="bg-gray-800 p-6 rounded-lg shadow-lg w-96 relative modal-content">
    <button id="closeCoverPhotoModal" class="absolute top-2 right-2 bg-gray-300 text-black rounded-full p-2">✖</button>
    <h3 class="text-2xl font-semibold mb-4 text-center text-white">Change  Photo</h3>

    <div class="flex flex-col items-center">
      <img id="coverPhotoPreview" src="<?php echo $cover_photo; ?>" alt="Cover Preview" class="w-full h-40 object-cover mb-4 rounded">
      <input type="file" id="coverPhotoInput" accept="image/*" class="mb-4">
      <div class="flex space-x-4">
      <button id="uploadCoverPhotoBtn" class="btn-primary">Done</button>
<button id="removeCoverPhotoBtn" class="btn-primary remove-btn">Remove</button>

      </div>
    </div>
  </div>
</div>

<!-- Image Zoom Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-70 hidden flex justify-center items-center z-50">
  <div class="relative">
    <img id="modalImage" src="" class="max-w-full max-h-screen rounded-lg transform scale-75 transition-all duration-300">
    <button id="closeModal" class="absolute top-4 right-4 bg-gray-300 text-black rounded-full p-2">✖</button>
  </div>
</div>
<script>
$(document).ready(function(){
  // Open modals
  $("#editProfileBtn").click(function(){
    $("#editProfileModal").removeClass("hidden").hide().fadeIn(300);
  });
  $("#editProfilePhotoBtn").click(function(){
    $("#editProfilePhotoModal").removeClass("hidden").hide().fadeIn(300);
  });
  $("#editCoverBtn").click(function(){
    $("#editCoverPhotoModal").removeClass("hidden").hide().fadeIn(300);
  });

  // Close modals with fade-out effect
  $("#closeEditProfile").click(function(){
    $("#editProfileModal").fadeOut(300, function() {
      $(this).addClass("hidden");
    });
  });
  $("#closeProfilePhotoModal").click(function(){
    $("#editProfilePhotoModal").fadeOut(300, function() {
      $(this).addClass("hidden");
    });
  });
  $("#closeCoverPhotoModal").click(function(){
    $("#editCoverPhotoModal").fadeOut(300, function() {
      $(this).addClass("hidden");
    });
  });

  // Preview Profile Photo on file select
  $("#profilePhotoInput").change(function(){
    let file = this.files[0];
    if(file){
      let reader = new FileReader();
      reader.onload = function(e){
        $("#profilePhotoPreview").attr("src", e.target.result);
      }
      reader.readAsDataURL(file);
    }
  });
  
  // Upload Profile Photo via AJAX
  $("#uploadProfilePhotoBtn").click(function(){
    let fileInput = $("#profilePhotoInput")[0];
    if(fileInput.files.length === 0){
      alert("Pumili ng larawan muna.");
      return;
    }
    let formData = new FormData();
    formData.append("profile_picture", fileInput.files[0]);
    $.ajax({
      url: 'upload.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response){
        if(response.trim() === 'success'){
          $("#profilePicDisplay").attr("src", $("#profilePhotoPreview").attr("src"));
          $("#editProfilePhotoModal").addClass("hidden");
          alert("Profile photo updated!");
        } else {
          alert("Upload failed: " + response);
        }
      }
    });
  });
  

  // Preview Cover Photo on file select
  $("#coverPhotoInput").change(function(){
    let file = this.files[0];
    if(file){
      let reader = new FileReader();
      reader.onload = function(e){
        $("#coverPhotoPreview").attr("src", e.target.result);
      }
      reader.readAsDataURL(file);
    }
  });
  
  // Upload Cover Photo via AJAX
  $("#uploadCoverPhotoBtn").click(function(){
    let fileInput = $("#coverPhotoInput")[0];
    if(fileInput.files.length === 0){
      alert("Pumili ng cover photo muna.");
      return;
    }
    let formData = new FormData();
    formData.append("cover_photo", fileInput.files[0]);
    $.ajax({
      url: 'upload.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response){
        if(response.trim() === 'success'){
          $("#coverPhotoDisplay").attr("src", $("#coverPhotoPreview").attr("src"));
          $("#editCoverPhotoModal").addClass("hidden");
          alert("Cover photo updated!");
        } else {
          alert("Upload failed: " + response);
        }
      }
    });
  });
  
// Delete Profile Photo
$("#removeProfilePhotoBtn").click(function(){
  $.ajax({
    url: 'delete.php',
    type: 'POST',
    data: { delete_profile_picture: true },
    success: function(response){
      if(response.trim() === 'success'){
        alert("Profile photo removed!");
        location.reload(); // Auto-refresh the page
      } else {
        alert("Delete failed: " + response);
      }
    }
  });
});

// Delete Cover Photo
$("#removeCoverPhotoBtn").click(function(){
  $.ajax({
    url: 'delete.php',
    type: 'POST',
    data: { delete_cover_photo: true },
    success: function(response){
      if(response.trim() === 'success'){
        alert("Cover photo removed!");
        location.reload(); // Auto-refresh the page
      } else {
        alert("Delete failed: " + response);
      }
    }
  });
});


  
  // Edit Profile Form Submission via AJAX
  $("#editProfileForm").submit(function(e){
    e.preventDefault();
    let formData = $(this).serialize();
    $.ajax({
      url: 'update_profile.php',
      type: 'POST',
      data: formData,
      success: function(response){
        if(response.trim() === 'success'){
          alert("Profile updated successfully!");
          location.reload();
        } else {
          alert("Update failed: " + response);
        }
      }
    });
  });
});

$(document).ready(function() {
  // Function to show image modal
  function showImageModal(imgSrc) {
    $("#modalImage").attr("src", imgSrc);
    $("#imageModal").removeClass("hidden");
    setTimeout(() => {
      $("#modalImage").removeClass("scale-75").addClass("scale-100");
    }, 10);
  }

  // Click event for profile picture
  $("#profilePicDisplay").click(function() {
    showImageModal($(this).attr("src"));
  });

  // Click event for cover photo
  $("#coverPhotoDisplay").click(function() {
    showImageModal($(this).attr("src"));
  });

  // Close modal
  $("#closeModal").click(function() {
    $("#modalImage").removeClass("scale-100").addClass("scale-75");
    setTimeout(() => {
      $("#imageModal").addClass("hidden");
    }, 300);
  });

  // Close modal on background click
  $("#imageModal").click(function(e) {
    if (e.target === this) {
      $("#closeModal").click();
    }
  });
});
document.addEventListener("DOMContentLoaded", function () {
    const numParticles = 30; // Adjust number of particles
    const container = document.body;

    for (let i = 0; i < numParticles; i++) {
        let particle = document.createElement("div");
        particle.classList.add("particle");
        particle.style.left = Math.random() * window.innerWidth + "px";
        particle.style.top = Math.random() * window.innerHeight + "px";
        particle.style.animationDuration = 2 + Math.random() * 3 + "s"; // Random speed
        particle.style.animationDelay = Math.random() * 2 + "s"; // Random delay
        container.appendChild(particle);
    }
});
</script>

</body>
</html>

<style>/* Modernized Profile Page Styles */
body {
  font-family: 'Inter', sans-serif;
  background: url('choosing-bg11.jpg') center/cover;
  color: #fff;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  margin: 0;
}

.container {
  max-width: 800px;  /* Decreased max-width for smaller sides */
  width: 100%;
  background: #fff;
  color: #333;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: fadeIn 1s ease-in-out;
  padding: 20px;  /* Reduced padding to make it tighter */
}

.cover-photo {
  width: 100%;
  height: 250px;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.cover-photo:hover {
  transform: scale(1.03);
}

.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 5px solid white;
  position: absolute;
  top: 180px;
  left: 30px;
  transition: transform 0.4s ease;
}

.profile-pic:hover {
  transform: scale(1.1);
  animation: bounce 0.6s ease-in-out infinite alternate;
}

.profile-info {
  padding: 50px 30px 30px;
  text-align: left;
}

.profile-info h2 {
  font-size: 28px;
  font-weight: 700;
  color: #222;
  animation: slideIn 1s ease-in-out;
}

.profile-info p {
  font-size: 16px;
  color: #666;
  margin-top: 5px;
  animation: fadeIn 1.2s ease-in-out;
}

.btn-primary {
  background: linear-gradient(45deg, #6a11cb, #2575fc);
  border: none;
  color: white;
  padding: 10px 20px;
  border-radius: 30px;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(45deg, #2575fc, #6a11cb);
  box-shadow: 0 4px 15px rgba(38, 100, 255, 0.3);
}

.remove-btn {
  background: linear-gradient(45deg, #fc3d57, #ff5f6d); /* Red gradient */
}

.remove-btn:hover {
  background: linear-gradient(45deg, #ff5f6d, #fc3d57);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes bounce {
  from { transform: translateY(0); }
  to { transform: translateY(-5px); }
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(-30px); }
  to { opacity: 1; transform: translateX(0); }
}

@keyframes popUp {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

.modal {
  opacity: 0;
  transform: scale(0.95);
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal:not(.hidden) {
  opacity: 1;
  transform: scale(1);
}

/* Responsive Styles */
@media (max-width: 768px) {
  .container {
    padding: 15px;
  }

  .profile-pic {
    width: 100px;
    height: 100px;
    top: 150px; /* Adjusted position */
    left: 20px;
  }

  .profile-info h2 {
    font-size: 24px;
  }

  .profile-info p {
    font-size: 14px;
  }

  .btn-primary {
    padding: 8px 15px;
    font-size: 14px;
  }

  .cover-photo {
    height: 200px; /* Adjusted height */
  }
}

@media (max-width: 480px) {
  body {
    padding: 10px;
  }

  .container {
    width: 100%;
    max-width: none;
    padding: 10px;
  }

  .profile-pic {
    width: 80px;
    height: 80px;
    top: 120px; /* Adjusted position */
    left: 10px;
  }

  .profile-info h2 {
    font-size: 20px;
  }

  .profile-info p {
    font-size: 12px;
  }

  .btn-primary {
    padding: 6px 12px;
    font-size: 12px;
  }

  .cover-photo {
    height: 150px; /* Adjusted height */
  }
}


</style>