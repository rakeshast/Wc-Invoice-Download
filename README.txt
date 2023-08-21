# WordPress WC PDF Invoice Download Plugin

The WordPress PDF Invoice Download Plugin is a powerful tool designed to facilitate the generation and download of PDF Invoices in PDF format for both customers and administrators. This plugin streamlines the invoicing process, making it easier to manage transactions and provide a professional experience for users.

## Features

- **Generate PDF Invoices**: Automatically generate PDF Invoices based on customer orders and transaction details.

- **PDF Format**: PDF Invoices are generated in PDF format, ensuring a standardized and easily shareable document.

- **Customer Access**: Allow customers to access and download their PDF Invoices directly from their account dashboard.

- **Admin Control**: Administrators have the ability to generate and download PDF Invoices for all transactions, providing a comprehensive overview of the financial status.

- **Customization**: Customize PDF Invoice templates with your company's branding, including logos, colors, and contact information.

- **Secure**: PDF Invoices contain only relevant transaction information, ensuring customer privacy and data security.

## Installation

1. **Download**: Download the plugin ZIP file from the official WordPress Plugin Repository or your preferred source.

2. **Upload**: Log in to your WordPress admin panel, navigate to "Plugins" -> "Add New," and click on the "Upload Plugin" button. Select the downloaded ZIP file and click "Install Now."

3. **Activate**: After installation, activate the plugin through the "Plugins" menu.


## Usage

### For Customers

1. **Account Dashboard**: Once the plugin is installed and configured, customers can log in to their accounts and navigate to the "Orders" section.

2. **PDF Invoice Download**: In the "Orders" section, customers will see a list of their transactions. Each transaction will have an associated "Download PDF" button. Clicking this button will initiate the download of the PDF Invoice in PDF format.

### For Administrators

1. **Admin Dashboard**: Administrators can generate and access PDF Invoices from the WordPress admin dashboard.

2. **Download PDF Invoice**: After generating the PDF Invoice, an "Download PDF Invoice" button will appear on the order details page. Clicking this button will download the PDF Invoice in PDF format.

## HOOKS

The plugin provides options to customize the appearance of the generated PDF Invoices:

- **Hooks**: Use hook to add new order items in pdf table,
add_action( wcpdfd_order_invoice_dpf_add_extra_order_details, ‘callback_function’, 10, 1 );


## Support

If you encounter any issues or have questions regarding the plugin's functionality or usage, please refer to the plugin documentation or contact our support team at support@example.com.

## Conclusion

The WordPress PDF Invoice Download Plugin simplifies the invoicing process by allowing customers to easily access and download their PDF Invoices while providing administrators with a comprehensive tool for managing transaction records. With its customization options and PDF format support, this plugin enhances the professionalism of your online business and improves user experience.