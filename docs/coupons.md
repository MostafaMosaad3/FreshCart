# Coupons

## Stacking policy
Single coupon per order. `carts.coupon_id` (carried onto the order at checkout)
holds one coupon; applying a second **replaces** the first. Stacking
("stackable by kind") would need a `coupon_order` pivot and negative-total
guards — a real change, not a config flag.

## Buy-X-Get-Y
Frees the **cheapest** eligible units (customer-positive). To flip to
most-expensive-free, change `BuyXGetYDiscount` only — it is the single source of truth.

## Compute vs consume
The discount is **computed** in the `CalculatePrice` stage (so payment charges the
right amount) but the coupon is **consumed** in the `RedeemCoupon` stage, after
payment succeeds and inside the checkout transaction. This prevents phantom
redemptions — a counter that went up for an order that never completed.

## Over-redemption guard
`RedeemCoupon` increments `used_count` with an atomic
`whereRaw('used_count < max_uses')` guard. If the update affects zero rows the
coupon is maxed out and the stage throws `CouponMaxedOutException`, rolling back
the whole checkout transaction. No explicit lock needed — the database serializes
the atomic update.

## Data model
`coupons.strategy` (string) + `coupons.config` (JSON). New discount type = a new
strategy class + one row in `config/discounts.php`. No migration. The strategy
class owns validation of its own `config` shape.

## Eligibility
`Coupon::isValidForCart(User, Cart)` returns a `CouponValidationResult` with a
precise `reason` (`inactive`, `expired`, `not_started`, `max_uses_reached`,
`below_min_subtotal`, `not_first_order`, `user_limit_reached`). It stays a model
method until rules multiply past ~6 — then promote to Specification classes.
