<style>
  .profile-detail button {
    border-radius: 50%; 
    padding-inline: 10px; 
    padding-top: 4px; 
    background-color: var(--dark-blue);
  }
  .profile-image {
    position: relative;
    display: inline-block;
  }

  .profile-image img {
    display: block;
    height: auto;
  }

  .overlay {  
    position: absolute;  
    top: 0;  
    left: 0;  
    right: 0;  
    bottom: 0;  
    background-color: rgba(0, 0, 0, 0.9);  
    opacity: 0;  
    border-radius: 50%;  
    transition: opacity 0.7s;  
    display: flex;  
    justify-content: center;  
    align-items: center;  
  }  

  .overlay .icon {  
    opacity: 0;  
    transition: opacity 0.7s, transform 0.3s;  
    font-size: 24px; 
  }  

  .profile-image:hover .overlay {  
    opacity: 1;  
    cursor: pointer;
  }  

  .profile-image:hover .overlay .icon {  
    opacity: 1;  
    transform: scale(1);
  }

  @media only screen and (max-width: 990px) {
    #account-page-container {
      margin-bottom: 22px;
    }
  }

</style>
