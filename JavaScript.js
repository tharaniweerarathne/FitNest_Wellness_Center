// Add this JavaScript (can be in a separate file or in a script tag)
document.getElementById('bmiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const height = parseFloat(document.getElementById('height').value) / 100; // convert cm to m
    const weight = parseFloat(document.getElementById('weight').value);
    
    if (height <= 0 || weight <= 0) {
        document.getElementById('bmi-result').textContent = "Please enter valid values";
        document.getElementById('bmi-result').style.backgroundColor = "rgba(255,0,0,0.1)";
        return;
    }
    
    const bmi = weight / (height * height);
    const resultElement = document.getElementById('bmi-result');
    
    let category = '';
    if (bmi < 18.5) {
        category = 'Underweight';
        resultElement.style.backgroundColor = "rgba(0,150,255,0.2)";
    } else if (bmi < 25) {
        category = 'Normal weight';
        resultElement.style.backgroundColor = "rgba(0,255,0,0.2)";
    } else if (bmi < 30) {
        category = 'Overweight';
        resultElement.style.backgroundColor = "rgba(255,165,0,0.2)";
    } else {
        category = 'Obese';
        resultElement.style.backgroundColor = "rgba(255,0,0,0.2)";
    }
    
    resultElement.innerHTML = `Your BMI: <span style="font-size:1.2em">${bmi.toFixed(1)}</span><br>${category}`;
});


// function to toggle profile options visibility
function toggleProfileOptions(event) {
    let profileOptions = document.getElementById("profileOptions");
  
    if (profileOptions.style.display === "block") {
      profileOptions.style.display = "none";
    } else {
      profileOptions.style.display = "block";
    }
  
  
    event.stopPropagation();
  }
  
  document.addEventListener("click", function(event) {
    let profileOptions = document.getElementById("profileOptions");
    let profileSection = document.querySelector(".profile-section");
  
    if (!profileSection.contains(event.target)) {
      profileOptions.style.display = "none";
    }
  });
  
  
  
  function showEditForm(id) {
    document.getElementById('editForm_' + id).style.display = 'block';
  }
  
  function hideEditForm(id) {
    document.getElementById('editForm_' + id).style.display = 'none';
  }
  



 

document.addEventListener('DOMContentLoaded', function() {
  console.log('Testimonials script loaded');
  
  const track = document.querySelector('.testimonials-slide');
  const prevBtn = document.querySelector('.prev-btn');
  const nextBtn = document.querySelector('.next-btn');
  const cards = document.querySelectorAll('.testimonial-card');
  
  // Debug info
  console.log('Track found:', !!track);
  console.log('Prev button found:', !!prevBtn);
  console.log('Next button found:', !!nextBtn);
  console.log('Number of cards:', cards.length);
  
  // exit if elements aren't found
  if (!track || !prevBtn || !nextBtn || cards.length === 0) {
      console.error('Carousel elements not found');
      return;
  }
  
  let currentIndex = 0;
  const totalCards = cards.length;
  let cardWidth = getCardWidth();
  
  // calculate card width including margins
  function getCardWidth() {
      const card = cards[0];
      const styles = window.getComputedStyle(card);
      const width = card.offsetWidth + 
             parseInt(styles.marginLeft) + 
             parseInt(styles.marginRight);
      console.log('Card width calculated as:', width);
      return width;
  }
  
  // update carousel position
  function updateCarousel() {
      const translateX = -currentIndex * cardWidth;
      console.log('Updating carousel to position:', translateX);
      track.style.transform = `translateX(${translateX}px)`;
      
      // update button visual states
      prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
      nextBtn.style.opacity = currentIndex >= totalCards - 1 ? '0.5' : '1';
  }
  
  updateCarousel();
  
  // previous button handler with direct function definition
  prevBtn.onclick = function() {
      console.log('Previous button clicked');
      if (currentIndex > 0) {
          currentIndex--;
          updateCarousel();
      }
  };
  

  nextBtn.onclick = function() {
      console.log('Next button clicked');
      if (currentIndex < totalCards - 1) {
          currentIndex++;
          updateCarousel();
      }
  };
  

  window.addEventListener('resize', function() {
      cardWidth = getCardWidth();
      updateCarousel();
  });
  

  let touchStartX = 0;
  let touchEndX = 0;
  
  track.addEventListener('touchstart', function(e) {
      touchStartX = e.changedTouches[0].screenX;
  }, {passive: true});
  
  track.addEventListener('touchend', function(e) {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
  }, {passive: true});
  
  function handleSwipe() {
      const swipeThreshold = 50; 
      
      if (touchStartX - touchEndX > swipeThreshold) {

          nextBtn.onclick();
      } else if (touchEndX - touchStartX > swipeThreshold) {

          prevBtn.onclick();
      }
  }
  

  prevBtn.addEventListener('mousedown', function() {
      console.log('Previous button mousedown event');
  });
  
  nextBtn.addEventListener('mousedown', function() {
      console.log('Next button mousedown event');
  });
});