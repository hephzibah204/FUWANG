# Logistics and Delivery Agent Integration Plan

## 1. Core Principles

*   **In-House First:** The system will prioritize the in-house `DeliveryAgent` network for local and regional deliveries. This reduces costs and builds your brand.
*   **Scalable 3rd-Party Integration:** The architecture will allow for the seamless addition of any number of external shipping companies through a standardized interface.
*   **Complete Admin Control:** The admin dashboard will provide the functionality to add, configure, and enable/disable any shipping provider with a single click.

## 2. System Architecture

### 2.1. `DeliveryAgent` Model & Logic
This will be the cornerstone of the in-house network.

*   **Model (`app/Models/DeliveryAgent.php`):**
    *   `user_id`: Foreign key linking to the `User` model.
    *   `state`, `city`: For location-based routing.
    *   `availability_status`: (e.g., `available`, `on_delivery`, `offline`).
    *   `rating`: A performance metric based on completed deliveries.
    *   `approval_status`: (`pending`, `approved`, `rejected`) for admin moderation.
*   **Registration:** The user registration process will be modified to include an "Apply as Delivery Agent" checkbox.

### 2.2. `ShippingProvider` Model & Logic
This will manage all third-party logistics companies.

*   **Model (`app/Models/ShippingProvider.php`):**
    *   `name`: The provider's name (e.g., "GIG Logistics").
    *   `api_key`, `api_secret`: Encrypted credentials for API access.
    *   `api_base_url`: The base URL for their API endpoints.
    *   `is_active`: A boolean toggle for admin to enable or disable the provider.

### 2.3. `LogisticsDispatchService`
This service will be the brain of the operation, deciding how to route each shipment.

*   **Logic Flow:**
    1.  A new shipment is requested.
    2.  The service queries the `DeliveryAgent` model to find an `approved` and `available` agent in the recipient's `state` and `city`.
    3.  **If a local agent is found:** The shipment is assigned to the agent.
    4.  **If no local agent is found:** The service queries all `ShippingProvider`s where `is_active` is `true`. It fetches rates from each provider and presents the best option to the user or books it automatically, based on admin settings.

## 3. Research: Nigerian Logistics APIs

Here is a list of potential logistics companies in Nigeria that likely offer APIs for integration. When exploring them, look for "Developer," "API Reference," or "Integration" sections on their websites.

*   **GIG Logistics (GIGL):** Highly recommended to check first. They are known for a strong technology platform and extensive network. (Search: "GIGL API documentation").
*   **Kwik Delivery:** Excellent for on-demand, last-mile deliveries within major cities. (Search: "Kwik Delivery API").
*   **MaxNG:** Strong in motorcycle-based logistics, ideal for urban deliveries. (Search: "MaxNG delivery API").
*   **ACE Logistics (ace.ng):** A well-established logistics provider in Nigeria.
*   **DHL Express, FedEx, UPS:** These global leaders have a strong presence in Nigeria and offer mature, well-documented APIs for national and international shipping. (Search: "DHL Developer Portal", "FedEx API Documentation").

### Key API Features to Look For:
*   **Rate Calculation:** An endpoint to get shipping quotes based on origin, destination, and package weight/size.
*   **Shipment Creation:** An endpoint to programmatically book a delivery and generate a waybill.
*   **Real-Time Tracking:** An endpoint or a webhook system to receive updates on the shipment's status.
