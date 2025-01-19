## Order process planning

**When place an order from checkout page these steps should be followed.**

1. `query campaign` endpoint will be called.
    - get campaign details.
    - extract products from campaign.
    - match product with order items product sku.
    - get campaign id and product id from konnektive crm matched product.
2. populate the `import order` payload with order data.
3. call `import order` endpoint.
    - if return any error show error message on checkout page.
