  
       <style>
.loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
  display: none; /* Initially hidden */
  justify-content: center;
  align-items: center;
  z-index: 9999; /* Ensure it's on top of other elements */
}

/* CSS for the pulsating animation */
@keyframes rotate {
  0% {
    transform: rotate(0deg); /* Start rotation from 0 degrees */
  }
  100% {
    transform: rotate(360deg); /* End rotation at 360 degrees */
  }
}

.loader::after {
  content: '';
  display: block;
  width: 60px; /* Adjust the size of the spinner */
  height: 60px;
  border-radius: 50%;
  border: 6px solid red; /* Spinner color */
  border-color: <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent <?php echo htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8'); ?> transparent; /* Spinner color pattern */
  animation: rotate 1.5s linear infinite; /* Rotate animation with infinite loop */
}
</style>
    
    <div class="loader" id="loader"></div>

<script>
  let loaderTimeout; // Variable to store the timeout reference

function showLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'flex'; // Show the loader

  // Set a timeout to hide the loader after 30 seconds
  loaderTimeout = setTimeout(function () {
    hideLoader();
  }, 1000);
}

function hideLoader() {
  var loader = document.getElementById('loader');
  loader.style.display = 'none'; // Hide the loader

  // Clear the previous timeout if it exists
  clearTimeout(loaderTimeout);
}


  // Show the loader when the page starts loading
    showLoader();


    // Add an event listener for when the page has finished loading
    window.onload = function () {
      hideLoader(); // Hide the loader when the page has finished loading
    }

</script><script>
document.addEventListener('DOMContentLoaded', function () {
  // Get the form and submit button elements
  var form = document.querySelector('form');
  var submitButton = document.querySelector('button[type="submit"]');

  // Add a submit event listener to the form
  form.addEventListener('submit', function () {
    // Add the spinner HTML to the inner HTML of the submit button
    if (submitButton) {
      submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" style="color: #092C9F;"></span> <b style="color: #092C9F;">Processing...</b>';
    }

    // You can also perform additional actions here based on the form submission
    // For example, you can use AJAX to submit the form data asynchronously.
  });
});


document.addEventListener('DOMContentLoaded', function () {
  // Get all <a> elements on the page
  var aElements = document.querySelectorAll('a');

  // Add a click event listener to each <a> element
  aElements.forEach(function(aElement) {
    aElement.addEventListener('click', function (event) {
      // Check if the href attribute is empty or equals to "#"
      if (aElement.getAttribute('href') !== "" && aElement.getAttribute('href') !== "#") {
        showLoader();
      }
      // Prevent the default behavior of the link (e.g., following the href)

    });
  });
});
</script>