# Stage 10 Provider Research Evidence

## Zibal — Primary

Official sources:

- https://help.zibal.ir/ipg/
- https://gateway.zibal.ir
- https://zibal.ir/ipg/refund

The official IPG request flow uses `POST https://gateway.zibal.ir/v1/request` with merchant, amount in IRR, callbackUrl and optional orderId. A successful request returns `result:100` and `trackId`; redirect is `https://gateway.zibal.ir/start/{trackId}`. Callback is only a notification. Server-side `POST /v1/verify` is required; result `100` is success and `201` means already verified. Inquiry is available for ambiguous status. The docs do not publish an idempotency key, so the application uses an immutable internal order key, unique local records and duplicate-safe verification. Merchant is treated as a server secret.

## Zarinpal — Alternative

Official sources:

- https://www.zarinpal.com/docs/paymentGateway/connectToGateway
- https://www.zarinpal.com/docs/paymentGateway/errorList
- https://www.zarinpal.com/docs/paymentGateway/moreFeatures/currency
- https://www.zarinpal.com/docs/paymentGateway/moreFeatures/session-validation
- https://www.zarinpal.com/docs/apiDocs/query/refund
- https://www.zarinpal.com/docs/apiDocs/

The official request endpoint is `POST https://payment.zarinpal.com/pg/v4/payment/request.json`; response code `100` returns Authority and redirect uses `/pg/StartPay/{authority}`. Callback Status `OK` only starts server-side verify. Verify code `100` is new success and `101` is already verified. Amount and currency must be taken from the immutable local payment record. The docs do not publish a general request idempotency key; local order/authority uniqueness is required.

## IDPay — Alternative

Official sources:

- https://idpay.ir/web-service/v1.1/
- https://api.idpay.ir/v1.1/payment
- https://api.idpay.ir/v1.1/payment/verify
- https://api.idpay.ir/v1.1/payment/inquiry
- https://panel.idpay.ir/web-services

The official v1.1 request endpoint is `POST https://api.idpay.ir/v1.1/payment` using `X-API-KEY`; success is documented as HTTP `201` with `id` and `link`. Verify uses the same id and order_id, with status `100` success and `101` already verified. The docs do not publish an idempotency key or independent refund endpoint; local uniqueness and inquiry are required.

## Decision

Zibal is Primary per Founder instruction. Domain code depends only on `PaymentGateway`; Provider adapters are replaceable. No credentials or SDK dependency is stored in Git.
