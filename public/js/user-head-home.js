tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#513B2E", // A rich, dark coffee brown
              "background-light": "#F5F0E8", // A creamy off-white, like latte foam
              "background-dark": "#1F1A17", // A very dark brown, like dark roast beans
              "card-light": "#FFFFFF",
              "card-dark": "#2C2521",
              "text-light": "#513B2E",
              "text-dark": "#DCD0C0",
              "text-muted-light": "#755F4D",
              "text-muted-dark": "#A08C78"
            },
            fontFamily: {
              display: ["Playfair Display", "serif"],
              sans: ["Poppins", "sans-serif"],
            },
            borderRadius: {
              DEFAULT: "0.5rem", // 8px
              lg: "1rem", // 16px
              xl: "1.5rem" // 24px
            },
          },
        },
      };